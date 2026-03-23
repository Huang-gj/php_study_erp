<?php
declare (strict_types = 1);

namespace app\controller\sales;

use app\BaseController;
use think\db\Query;
use think\exception\ValidateException;
use think\facade\Db;
use think\response\Json;

class Report extends BaseController
{
    public function summary(): Json
    {
        $authResult = $this->checkAuthorization();
        if ($authResult !== null) {
            return $authResult;
        }

        $today = date('Y-m-d');
        $baseQuery = Db::name('sales_order')->where('del_state', 0);

        return $this->successResponse('查询成功', [
            'order_total' => (int) (clone $baseQuery)->count(),
            'today_finished_total' => (int) (clone $baseQuery)
                ->where('order_state', 7)
                ->where('delivery_date', $today)
                ->count(),
            'production_total' => (int) (clone $baseQuery)
                ->whereIn('order_state', [2, 3, 4, 5, 6])
                ->count(),
            'completed_total' => (int) (clone $baseQuery)
                ->where('order_state', 7)
                ->count(),
        ]);
    }

    public function orderList(): Json
    {
        $authResult = $this->checkAuthorization();
        if ($authResult !== null) {
            return $authResult;
        }

        $payload = $this->getRequestData();

        try {
            $this->validate($payload, [
                'contract_no' => 'max:64',
                'customer_name' => 'max:128',
                'product_spec' => 'max:128',
                'order_date' => 'dateFormat:Y-m-d',
                'page' => 'integer|egt:1',
                'page_size' => 'integer|between:1,200',
            ], [
                'contract_no.max' => '合同编号长度不能超过 64 个字符',
                'customer_name.max' => '客户名称长度不能超过 128 个字符',
                'product_spec.max' => '产品规格长度不能超过 128 个字符',
                'order_date.dateFormat' => '订单日期格式必须为 YYYY-MM-DD',
                'page.integer' => '页码必须为整数',
                'page.egt' => '页码必须大于等于 1',
                'page_size.integer' => '每页数量必须为整数',
                'page_size.between' => '每页数量必须在 1 到 200 之间',
            ]);
        } catch (ValidateException $exception) {
            return $this->errorResponse($exception->getMessage());
        }

        $page = max(1, (int) ($payload['page'] ?? 1));
        $pageSize = max(1, min(200, (int) ($payload['page_size'] ?? 10)));

        $query = $this->buildOrderListQuery($payload);
        $total = (int) (clone $query)->count();
        $rows = (clone $query)
            ->order('so.order_date', 'desc')
            ->order('so.id', 'desc')
            ->page($page, $pageSize)
            ->select()
            ->toArray();

        $list = array_map(function (array $row): array {
            $auditStatus = $this->mapOrderStateText((int) $row['order_state']);
            $productName = trim((string) ($row['product_name'] ?? ''));
            $specification = trim((string) ($row['specification'] ?? ''));
            $orderQuantity = trim((string) ($row['order_quantity'] ?? ''));
            $unitPrice = trim((string) ($row['unit_price'] ?? ''));

            $shipQuantity = trim((string) ($row['ship_quantity'] ?? ''));
            if ($shipQuantity === '') {
                $shipQuantity = ((float) ($row['shipped_quantity_total'] ?? 0) > 0)
                    ? $this->formatDecimal((string) $row['shipped_quantity_total'])
                    : '待装车';
            }

            $loadDate = trim((string) ($row['load_date'] ?? ''));
            if ($loadDate === '' || $loadDate === '0000-00-00') {
                $loadDate = '待装车';
            }

            return [
                'id' => (int) $row['id'],
                'sales_order_id' => (int) $row['sales_order_id'],
                'audit_state' => (int) $row['audit_state'],
                'audit_status' => $auditStatus,
                'contract_no' => (string) $row['contract_no'],
                'contract_text' => '合同编号：' . (string) $row['contract_no'],
                'customer_name' => (string) $row['customer_name'],
                'customer_info' => '客户名称：' . (string) $row['customer_name'],
                'order_date' => (string) $row['order_date'],
                'delivery_date' => (string) ($row['delivery_date'] ?? ''),
                'product_name' => $productName,
                'specification' => $specification,
                'order_quantity' => $orderQuantity,
                'unit_price' => $unitPrice,
                'ship_quantity' => $shipQuantity,
                'load_date' => $loadDate,
            ];
        }, $rows);

        return $this->successResponse('查询成功', [
            'list' => $list,
            'total' => $total,
            'page' => $page,
            'page_size' => $pageSize,
        ]);
    }

    protected function buildOrderListQuery(array $payload): Query
    {
        $contractNo = trim((string) ($payload['contract_no'] ?? ''));
        $customerName = trim((string) ($payload['customer_name'] ?? ''));
        $productSpec = trim((string) ($payload['product_spec'] ?? ''));
        $orderDate = trim((string) ($payload['order_date'] ?? ''));

        $itemSummarySql = Db::name('sales_order_item')
            ->alias('soi')
            ->where('soi.del_state', 0)
            ->field('soi.sales_order_id')
            ->fieldRaw("GROUP_CONCAT(soi.product_name ORDER BY soi.line_no SEPARATOR '\n') AS product_name")
            ->fieldRaw("GROUP_CONCAT(soi.product_spec ORDER BY soi.line_no SEPARATOR '\n') AS specification")
            ->fieldRaw("GROUP_CONCAT(TRIM(TRAILING '.' FROM TRIM(TRAILING '0' FROM CAST(soi.quantity AS CHAR))) ORDER BY soi.line_no SEPARATOR ' / ') AS order_quantity")
            ->fieldRaw("GROUP_CONCAT(TRIM(TRAILING '.' FROM TRIM(TRAILING '0' FROM CAST(soi.tax_price AS CHAR))) ORDER BY soi.line_no SEPARATOR ' / ') AS unit_price")
            ->fieldRaw("GROUP_CONCAT(CASE WHEN soi.shipped_quantity > 0 THEN TRIM(TRAILING '.' FROM TRIM(TRAILING '0' FROM CAST(soi.shipped_quantity AS CHAR))) ELSE '待装车' END ORDER BY soi.line_no SEPARATOR ' / ') AS ship_quantity")
            ->group('soi.sales_order_id')
            ->buildSql();

        $query = Db::name('sales_order')
            ->alias('so')
            ->leftJoin([$itemSummarySql => 'item_summary'], 'item_summary.sales_order_id = so.sales_order_id')
            ->field([
                'so.id',
                'so.sales_order_id',
                'so.contract_no',
                'so.customer_name',
                'so.order_date',
                'so.delivery_date',
                'so.load_date',
                'so.audit_state',
                'so.order_state',
                'so.shipped_quantity',
                'item_summary.product_name',
                'item_summary.specification',
                'item_summary.order_quantity',
                'item_summary.unit_price',
                'item_summary.ship_quantity',
            ])
            ->fieldRaw('so.shipped_quantity AS shipped_quantity_total')
            ->where('so.del_state', 0);

        if ($contractNo !== '') {
            $query->whereLike('so.contract_no', '%' . $contractNo . '%');
        }

        if ($customerName !== '') {
            $query->whereLike('so.customer_name', '%' . $customerName . '%');
        }

        if ($orderDate !== '') {
            $query->where('so.order_date', $orderDate);
        }

        if ($productSpec !== '') {
            $productSpecOrderIds = Db::name('sales_order_item')
                ->where('del_state', 0)
                ->whereLike('product_spec', '%' . $productSpec . '%')
                ->field('sales_order_id');

            $query->whereIn('so.sales_order_id', $productSpecOrderIds);
        }

        return $query;
    }

    protected function mapOrderStateText(int $orderState): string
    {
        $mapping = [
            0 => '待生产员审核',
            1 => '待销售主管审核',
            2 => '生产中',
            3 => '待报工',
            4 => '待入库',
            5 => '等待出库',
            6 => '待发货',
            7 => '已完成',
            8 => '已取消',
        ];

        return $mapping[$orderState] ?? '未知状态';
    }

    protected function formatDecimal(string $value): string
    {
        if ($value === '') {
            return '';
        }

        $formatted = rtrim(rtrim($value, '0'), '.');

        return $formatted === '' ? '0' : $formatted;
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
        return json([
            'code' => 0,
            'msg' => $msg,
            'data' => $data,
        ]);
    }

    protected function errorResponse(string $msg, array $data = [], int $code = 1001): Json
    {
        return json([
            'code' => $code,
            'msg' => $msg,
            'data' => $data,
        ]);
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
