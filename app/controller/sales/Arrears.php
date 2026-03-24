<?php
declare (strict_types = 1);

namespace app\controller\sales;

use app\BaseController;
use think\facade\Db;
use think\response\Json;

class Arrears extends BaseController
{
    protected $authClaims = [];

    public function list(): Json
    {
        $authResult = $this->ensureAuthorized();
        if ($authResult !== null) {
            return $authResult;
        }

        $payload = $this->getRequestData();
        $page = max(1, (int) ($payload['page'] ?? 1));
        $pageSize = max(1, min(200, (int) ($payload['page_size'] ?? 10)));

        $query = Db::name('sales_order')
            ->where('del_state', 0)
            ->where('unpaid_amount', '>', 0)
            ->field('customer_id')
            ->fieldRaw('MAX(customer_name) AS customer_name')
            ->group('customer_id');

        $total = (int) (clone $query)->count();
        $rows = (clone $query)
            ->order('customer_id', 'asc')
            ->page($page, $pageSize)
            ->select()
            ->toArray();

        $offset = ($page - 1) * $pageSize;
        $indexes = array_keys($rows);
        $list = array_map(function (array $row, $index): array {
            return [
                'rank_no' => $index + 1,
                'customer_id' => (int) $row['customer_id'],
                'customer_name' => (string) $row['customer_name'],
            ];
        }, $rows, $indexes);

        foreach ($list as &$item) {
            $item['rank_no'] += $offset;
        }

        return $this->successResponse('查询成功', [
            'list' => $list,
            'total' => $total,
            'page' => $page,
            'page_size' => $pageSize,
        ]);
    }

    public function detail(): Json
    {
        $authResult = $this->ensureAuthorized();
        if ($authResult !== null) {
            return $authResult;
        }

        $customerId = (int) (($this->getRequestData())['customer_id'] ?? 0);
        if ($customerId <= 0) {
            return $this->errorResponse('customer_id is required');
        }

        $rows = Db::name('sales_order')
            ->where('del_state', 0)
            ->where('customer_id', $customerId)
            ->where('unpaid_amount', '>', 0)
            ->order('order_date', 'desc')
            ->order('id', 'desc')
            ->select()
            ->toArray();

        if (empty($rows)) {
            return $this->errorResponse('Arrears detail not found');
        }

        $customerName = (string) ($rows[0]['customer_name'] ?? '');
        $totalUnpaidAmount = 0.0;
        $orderList = array_map(function (array $row) use (&$totalUnpaidAmount): array {
            $unpaidAmount = (float) $row['unpaid_amount'];
            $totalUnpaidAmount += $unpaidAmount;
            return [
                'sales_order_id' => (int) $row['sales_order_id'],
                'contract_no' => (string) $row['contract_no'],
                'order_date' => (string) $row['order_date'],
                'delivery_date' => (string) ($row['delivery_date'] ?? ''),
                'total_tax_amount' => $this->formatMoney((string) $row['total_tax_amount']),
                'received_amount' => $this->formatMoney((string) $row['received_amount']),
                'unpaid_amount' => $this->formatMoney((string) $row['unpaid_amount']),
            ];
        }, $rows);

        return $this->successResponse('查询成功', [
            'customer_id' => $customerId,
            'customer_name' => $customerName,
            'total_unpaid_amount' => $this->formatMoney((string) $totalUnpaidAmount),
            'order_list' => $orderList,
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
