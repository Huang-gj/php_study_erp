<?php
declare (strict_types = 1);

namespace app\controller\sales;

use app\BaseController;
use think\exception\ValidateException;
use think\facade\Db;
use think\response\Json;

class CustomerReconciliation extends BaseController
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

        $query = Db::name('sales_order')
            ->where('del_state', 0)
            ->field([
                'id',
                'sales_order_id',
                'customer_name',
                'total_tax_amount',
                'discount_amount',
                'logistics_fee',
                'order_date',
                'delivery_date',
                'payment_method',
                'invoice_required',
            ]);

        if ($customerName !== '') {
            $query->whereLike('customer_name', '%' . $customerName . '%');
        }

        if (is_array($dateRange) && count($dateRange) === 2) {
            $startDate = trim((string) ($dateRange[0] ?? ''));
            $endDate = trim((string) ($dateRange[1] ?? ''));
            if ($startDate !== '' && $endDate !== '') {
                $query->whereBetween('order_date', [$startDate, $endDate]);
            }
        }

        $total = (int) (clone $query)->count();
        $rows = (clone $query)
            ->order('id', 'desc')
            ->page($page, $pageSize)
            ->select()
            ->toArray();

        $list = array_map(function (array $row): array {
            return [
                'id' => (int) $row['id'],
                'sales_order_id' => (int) $row['sales_order_id'],
                'customer_name' => (string) $row['customer_name'],
                'total_tax_amount' => $this->formatMoney((string) $row['total_tax_amount']),
                'discount_amount' => $this->formatMoney((string) $row['discount_amount']),
                'logistics_fee' => $this->formatMoney((string) $row['logistics_fee']),
                'order_date' => (string) $row['order_date'],
                'delivery_date' => (string) ($row['delivery_date'] ?? ''),
                'payment_method_text' => $this->paymentMethodText((int) $row['payment_method']),
                'invoice_required_text' => (int) $row['invoice_required'] === 1 ? '是' : '否',
            ];
        }, $rows);

        return $this->successResponse('查询成功', [
            'list' => $list,
            'total' => $total,
            'page' => $page,
            'page_size' => $pageSize,
        ]);
    }

    protected function paymentMethodText(int $value): string
    {
        $mapping = [0 => '其他', 1 => '现金', 2 => '转账', 3 => '微信', 4 => '支付宝', 5 => '承兑'];
        return $mapping[$value] ?? '其他';
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
