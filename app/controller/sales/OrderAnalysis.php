<?php
declare (strict_types = 1);

namespace app\controller\sales;

use app\BaseController;
use think\exception\ValidateException;
use think\facade\Db;
use think\response\Json;

class OrderAnalysis extends BaseController
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
                'stat_year' => 'integer|egt:2000',
                'stat_month' => 'integer|between:1,12',
                'page' => 'integer|egt:1',
                'page_size' => 'integer|between:1,200',
            ]);
        } catch (ValidateException $exception) {
            return $this->errorResponse($exception->getMessage());
        }

        $page = max(1, (int) ($payload['page'] ?? 1));
        $pageSize = max(1, min(200, (int) ($payload['page_size'] ?? 10)));

        $query = Db::name('sales_report_order_month')->where('del_state', 0);
        if (($payload['stat_year'] ?? '') !== '') {
            $query->where('stat_year', (int) $payload['stat_year']);
        }
        if (($payload['stat_month'] ?? '') !== '') {
            $query->where('stat_month', (int) $payload['stat_month']);
        }

        $total = (int) (clone $query)->count();
        $rows = (clone $query)->order('stat_year', 'desc')->order('stat_month', 'asc')->page($page, $pageSize)->select()->toArray();

        $list = array_map(function (array $row): array {
            return [
                'id' => (int) $row['id'],
                'report_order_month_id' => (int) $row['report_order_month_id'],
                'stat_year' => (int) $row['stat_year'],
                'stat_month' => (int) $row['stat_month'],
                'month_text' => $this->monthText((int) $row['stat_year'], (int) $row['stat_month']),
                'total_product_quantity' => $this->formatDecimal((string) $row['total_product_quantity']),
                'total_ship_quantity' => $this->formatDecimal((string) $row['total_ship_quantity']),
                'total_order_count' => (int) $row['total_order_count'],
                'total_amount' => $this->formatMoney((string) $row['total_amount']),
                'remark' => (string) ($row['remark'] ?? ''),
            ];
        }, $rows);

        return $this->successResponse('查询成功', [
            'list' => $list,
            'total' => $total,
            'page' => $page,
            'page_size' => $pageSize,
        ]);
    }

    public function detail(): Json
    {
        $authResult = $this->checkAuthorization();
        if ($authResult !== null) {
            return $authResult;
        }

        $reportOrderMonthId = (int) (($this->getRequestData())['report_order_month_id'] ?? 0);
        if ($reportOrderMonthId <= 0) {
            return $this->errorResponse('report_order_month_id is required');
        }

        $row = Db::name('sales_report_order_month')
            ->where('del_state', 0)
            ->where('report_order_month_id', $reportOrderMonthId)
            ->find();
        if (!$row) {
            return $this->errorResponse('Order analysis data not found');
        }

        return $this->successResponse('查询成功', [
            'report_order_month_id' => (int) $row['report_order_month_id'],
            'stat_year' => (int) $row['stat_year'],
            'stat_month' => (int) $row['stat_month'],
            'month_text' => $this->monthText((int) $row['stat_year'], (int) $row['stat_month']),
            'total_product_quantity' => $this->formatDecimal((string) $row['total_product_quantity']),
            'total_ship_quantity' => $this->formatDecimal((string) $row['total_ship_quantity']),
            'total_order_count' => (int) $row['total_order_count'],
            'total_amount' => $this->formatMoney((string) $row['total_amount']),
            'remark' => (string) ($row['remark'] ?? ''),
        ]);
    }

    protected function monthText(int $year, int $month): string
    {
        $mapping = [1 => '一月', 2 => '二月', 3 => '三月', 4 => '四月', 5 => '五月', 6 => '六月', 7 => '七月', 8 => '八月', 9 => '九月', 10 => '十月', 11 => '十一月', 12 => '十二月'];
        return $year . '年' . ($mapping[$month] ?? ($month . '月'));
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
