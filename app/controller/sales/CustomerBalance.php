<?php
declare (strict_types = 1);

namespace app\controller\sales;

use app\BaseController;
use think\exception\ValidateException;
use think\facade\Db;
use think\response\Json;

class CustomerBalance extends BaseController
{
    protected $authClaims = [];

    public function list(): Json
    {
        $authResult = $this->ensureAuthorized();
        if ($authResult !== null) {
            return $authResult;
        }

        $payload = $this->getRequestData();
        try {
            $this->validate($payload, [
                'customer_name' => 'max:128',
                'page' => 'integer|egt:1',
                'page_size' => 'integer|between:1,200',
            ]);
        } catch (ValidateException $exception) {
            return $this->errorResponse($exception->getMessage());
        }

        $page = max(1, (int) ($payload['page'] ?? 1));
        $pageSize = max(1, min(200, (int) ($payload['page_size'] ?? 10)));
        $customerName = trim((string) ($payload['customer_name'] ?? ''));
        $dateRange = $payload['date_range'] ?? [];
        $startDate = '';
        $endDate = '';
        if (is_array($dateRange) && count($dateRange) === 2) {
            $startDate = trim((string) ($dateRange[0] ?? ''));
            $endDate = trim((string) ($dateRange[1] ?? ''));
        }

        $orderSubSql = Db::name('sales_order')
            ->alias('so')
            ->where('so.del_state', 0)
            ->field('so.customer_id')
            ->fieldRaw('MAX(so.id) AS max_id')
            ->fieldRaw('MAX(so.customer_name) AS customer_name')
            ->fieldRaw('COALESCE(SUM(so.total_tax_amount), 0) AS order_total_amount')
            ->fieldRaw('COALESCE(SUM(so.unpaid_amount), 0) AS order_unpaid_amount')
            ->when($customerName !== '', function ($query) use ($customerName) {
                $query->whereLike('so.customer_name', '%' . $customerName . '%');
            })
            ->group('so.customer_id')
            ->buildSql();

        $receiptSubSql = Db::name('sales_receipt')
            ->alias('sr')
            ->where('sr.del_state', 0)
            ->where('sr.audit_state', 1)
            ->field('sr.customer_id')
            ->fieldRaw('COALESCE(SUM(sr.receipt_amount), 0) AS receipt_total_amount')
            ->group('sr.customer_id')
            ->buildSql();

        $shipSubSql = Db::name('sales_outbound')
            ->alias('sob')
            ->where('sob.del_state', 0)
            ->where('sob.audit_state', 1)
            ->when($startDate !== '' && $endDate !== '', function ($query) use ($startDate, $endDate) {
                $query->whereBetween('sob.ship_date', [$startDate, $endDate]);
            })
            ->field('sob.customer_id')
            ->fieldRaw('COUNT(*) AS current_ship_total')
            ->group('sob.customer_id')
            ->buildSql();

        $query = Db::table($orderSubSql . ' ob')
            ->leftJoin([$receiptSubSql => 'rb'], 'rb.customer_id = ob.customer_id')
            ->leftJoin([$shipSubSql => 'sb'], 'sb.customer_id = ob.customer_id')
            ->fieldRaw('ob.max_id AS id')
            ->fieldRaw('ob.customer_id')
            ->fieldRaw('ob.customer_name')
            ->fieldRaw('ob.order_total_amount')
            ->fieldRaw('COALESCE(rb.receipt_total_amount, 0) AS receipt_total_amount')
            ->fieldRaw('ob.order_unpaid_amount')
            ->fieldRaw('COALESCE(sb.current_ship_total, 0) AS current_ship_total');

        $total = (int) (clone $query)->count();
        $rows = (clone $query)
            ->order('ob.max_id', 'desc')
            ->page($page, $pageSize)
            ->select()
            ->toArray();

        $summaryRow = Db::table($orderSubSql . ' ob')
            ->leftJoin([$receiptSubSql => 'rb'], 'rb.customer_id = ob.customer_id')
            ->leftJoin([$shipSubSql => 'sb'], 'sb.customer_id = ob.customer_id')
            ->fieldRaw('COALESCE(SUM(ob.order_total_amount), 0) AS total_order_total_amount')
            ->fieldRaw('COALESCE(SUM(COALESCE(rb.receipt_total_amount, 0)), 0) AS total_receipt_total_amount')
            ->fieldRaw('COALESCE(SUM(ob.order_unpaid_amount), 0) AS total_order_unpaid_amount')
            ->fieldRaw('COALESCE(SUM(COALESCE(sb.current_ship_total, 0)), 0) AS total_current_ship_total')
            ->find();

        $list = array_map(function (array $row): array {
            return [
                'id' => (int) $row['id'],
                'customer_id' => (int) $row['customer_id'],
                'customer_name' => (string) $row['customer_name'],
                'order_total_amount' => $this->formatMoney((string) $row['order_total_amount']),
                'receipt_total_amount' => $this->formatMoney((string) $row['receipt_total_amount']),
                'order_unpaid_amount' => $this->formatMoney((string) $row['order_unpaid_amount']),
                'current_ship_total' => (int) $row['current_ship_total'],
            ];
        }, $rows);

        return $this->successResponse('查询成功', [
            'list' => $list,
            'total' => $total,
            'page' => $page,
            'page_size' => $pageSize,
            'summary' => [
                'order_total_amount' => $this->formatMoney((string) ($summaryRow['total_order_total_amount'] ?? '0')),
                'receipt_total_amount' => $this->formatMoney((string) ($summaryRow['total_receipt_total_amount'] ?? '0')),
                'order_unpaid_amount' => $this->formatMoney((string) ($summaryRow['total_order_unpaid_amount'] ?? '0')),
                'current_ship_total' => (int) ($summaryRow['total_current_ship_total'] ?? 0),
            ],
        ]);
    }

    protected function formatMoney(string $value): string
    {
        return number_format((float) $value, 2, '.', '');
    }

    protected function ensureAuthorized(): ?Json
    {
        $authorization = (string) $this->request->header('Authorization', '');
        if ($authorization === '') {
            return $this->errorResponse('Unauthorized', [], 401);
        }

        $token = preg_replace('/^Bearer\s+/i', '', trim($authorization));
        if ($token === null || $token === '') {
            return $this->errorResponse('Unauthorized', [], 401);
        }

        try {
            $this->authClaims = jwt_parse_token($token);
        } catch (\Throwable $exception) {
            return $this->errorResponse('Unauthorized', [], 401);
        }

        return null;
    }

    protected function successResponse(string $msg, array $data = []): Json
    {
        return json(['code' => 0, 'msg' => $msg, 'data' => $data]);
    }

    protected function errorResponse(string $msg, array $data = [], int $code = 1001): Json
    {
        return json(['code' => $code, 'msg' => $msg, 'data' => $data]);
    }

    protected function getRequestData(): array
    {
        $payload = $this->request->post();
        if (!empty($payload)) {
            return $payload;
        }

        $content = $this->request->getContent();
        if (is_string($content) && $content !== '') {
            $jsonData = json_decode($content, true);
            if (is_array($jsonData)) {
                return $jsonData;
            }
        }

        $params = $this->request->param();
        return is_array($params) ? $params : [];
    }
}
