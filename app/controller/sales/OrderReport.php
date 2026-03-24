<?php
declare (strict_types = 1);

namespace app\controller\sales;

use app\BaseController;
use think\exception\ValidateException;
use think\facade\Db;
use think\response\Json;

class OrderReport extends BaseController
{
    public function list(): Json
    {
        $authResult = $this->checkAuthorization();
        if ($authResult !== null) {
            return $authResult;
        }

        $payload = $this->getRequestData();
        try {
            $this->validate($payload, [
                'customer_name' => 'max:128',
                'stat_date' => 'dateFormat:Y-m-d',
                'page' => 'integer|egt:1',
                'page_size' => 'integer|between:1,200',
            ]);
        } catch (ValidateException $exception) {
            return $this->errorResponse($exception->getMessage());
        }

        $page = max(1, (int) ($payload['page'] ?? 1));
        $pageSize = max(1, min(200, (int) ($payload['page_size'] ?? 10)));

        $query = Db::name('sales_report_customer_day')->where('del_state', 0);

        $customerName = trim((string) ($payload['customer_name'] ?? ''));
        if ($customerName !== '') {
            $query->whereLike('customer_name', '%' . $customerName . '%');
        }

        $statDate = trim((string) ($payload['stat_date'] ?? ''));
        if ($statDate !== '') {
            $query->where('stat_date', $statDate);
        }

        $summaryRow = (clone $query)
            ->fieldRaw('COALESCE(SUM(water_workshop_quantity), 0) AS total_water_workshop_quantity')
            ->fieldRaw('COALESCE(SUM(oil_workshop_quantity), 0) AS total_oil_workshop_quantity')
            ->fieldRaw('COALESCE(SUM(other_quantity), 0) AS total_other_quantity')
            ->fieldRaw('COALESCE(SUM(received_amount), 0) AS total_received_amount')
            ->fieldRaw('COALESCE(SUM(unpaid_amount), 0) AS total_unpaid_amount')
            ->find();

        $total = (int) (clone $query)->count();
        $rows = (clone $query)
            ->order('stat_date', 'desc')
            ->order('id', 'desc')
            ->page($page, $pageSize)
            ->select()
            ->toArray();

        $list = array_map(function (array $row): array {
            return [
                'id' => (int) $row['id'],
                'report_customer_day_id' => (int) $row['report_customer_day_id'],
                'stat_date' => (string) $row['stat_date'],
                'customer_id' => (int) $row['customer_id'],
                'customer_name' => (string) $row['customer_name'],
                'water_workshop_quantity' => $this->formatDecimal((string) $row['water_workshop_quantity']),
                'oil_workshop_quantity' => $this->formatDecimal((string) $row['oil_workshop_quantity']),
                'other_quantity' => $this->formatDecimal((string) $row['other_quantity']),
                'received_amount' => $this->formatMoney((string) $row['received_amount']),
                'unpaid_amount' => $this->formatMoney((string) $row['unpaid_amount']),
                'remark' => (string) ($row['remark'] ?? ''),
            ];
        }, $rows);

        return $this->successResponse('查询成功', [
            'list' => $list,
            'total' => $total,
            'page' => $page,
            'page_size' => $pageSize,
            'summary' => [
                'total_water_workshop_quantity' => $this->formatDecimal((string) ($summaryRow['total_water_workshop_quantity'] ?? '0')),
                'total_oil_workshop_quantity' => $this->formatDecimal((string) ($summaryRow['total_oil_workshop_quantity'] ?? '0')),
                'total_other_quantity' => $this->formatDecimal((string) ($summaryRow['total_other_quantity'] ?? '0')),
                'total_received_amount' => $this->formatMoney((string) ($summaryRow['total_received_amount'] ?? '0')),
                'total_unpaid_amount' => $this->formatMoney((string) ($summaryRow['total_unpaid_amount'] ?? '0')),
            ],
        ]);
    }

    protected function formatDecimal(string $value): string
    {
        if ($value === '') {
            return '';
        }

        $formatted = rtrim(rtrim($value, '0'), '.');
        return $formatted === '' ? '0' : $formatted;
    }

    protected function formatMoney(string $value): string
    {
        return number_format((float) $value, 2, '.', ',');
    }

    protected function checkAuthorization(): ?Json
    {
        $authorization = (string) $this->request->header('Authorization', '');
        if ($authorization === '') {
            return $this->errorResponse('未登录或登录已过期', [], 401);
        }

        $token = preg_replace('/^Bearer\s+/i', '', trim($authorization));
        if ($token === null || $token === '') {
            return $this->errorResponse('未登录或登录已过期', [], 401);
        }

        try {
            jwt_parse_token($token);
        } catch (\Throwable $exception) {
            return $this->errorResponse('未登录或登录已过期', [], 401);
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
