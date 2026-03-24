<?php
declare (strict_types = 1);

namespace app\controller\sales;

use app\BaseController;
use think\db\Query;
use think\exception\ValidateException;
use think\facade\Db;
use think\response\Json;

class ProductList extends BaseController
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
                'contract_no' => 'max:64',
                'customer_name' => 'max:128',
                'product_name' => 'max:128',
                'product_spec' => 'max:128',
                'order_date' => 'dateFormat:Y-m-d',
                'order_type' => 'integer|in:1,2',
                'ship_state' => 'integer|in:0,1,2',
                'page' => 'integer|egt:1',
                'page_size' => 'integer|between:1,200',
            ]);
        } catch (ValidateException $exception) {
            return $this->errorResponse($exception->getMessage());
        }

        $page = max(1, (int) ($payload['page'] ?? 1));
        $pageSize = max(1, min(200, (int) ($payload['page_size'] ?? 10)));
        $query = $this->buildListQuery($payload);
        $total = (int) (clone $query)->count();
        $rows = (clone $query)->order('so.order_date', 'desc')->order('soi.id', 'desc')->page($page, $pageSize)->select()->toArray();

        $list = array_map(function (array $row): array {
            return [
                'id' => (int) $row['id'],
                'sales_order_id' => (int) $row['sales_order_id'],
                'sales_order_item_id' => (int) $row['sales_order_item_id'],
                'contract_no' => (string) $row['contract_no'],
                'customer_name' => (string) $row['customer_name'],
                'order_date' => (string) $row['order_date'],
                'product_name' => (string) $row['product_name'],
                'product_spec' => (string) $row['product_spec'],
                'quantity' => $this->formatDecimal((string) $row['quantity']),
                'price' => $this->formatDecimal((string) $row['price']),
                'amount' => $this->formatDecimal((string) $row['amount']),
                'remark' => (string) ($row['remark'] ?? ''),
                'drawer_user_name' => (string) ($row['drawer_user_name'] ?? ''),
                'order_type' => (int) $row['order_type'],
                'order_type_text' => $this->orderTypeText((int) $row['order_type']),
                'ship_state' => (int) $row['ship_state'],
                'ship_state_text' => $this->shipStateText((int) $row['ship_state']),
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

        $salesOrderItemId = (int) (($this->getRequestData())['sales_order_item_id'] ?? 0);
        if ($salesOrderItemId <= 0) {
            return $this->errorResponse('sales_order_item_id is required');
        }

        $item = Db::name('sales_order_item')->where('del_state', 0)->where('sales_order_item_id', $salesOrderItemId)->find();
        if (!$item) {
            return $this->errorResponse('Sales product item not found');
        }

        $header = Db::name('sales_order')->where('del_state', 0)->where('sales_order_id', (int) $item['sales_order_id'])->find();
        if (!$header) {
            return $this->errorResponse('Sales order not found');
        }

        return $this->successResponse('查询成功', [
            'header' => [
                'sales_order_id' => (int) $header['sales_order_id'],
                'sales_order_item_id' => (int) $item['sales_order_item_id'],
                'contract_no' => (string) $header['contract_no'],
                'customer_name' => (string) $header['customer_name'],
                'order_date' => (string) $header['order_date'],
                'delivery_date' => (string) ($header['delivery_date'] ?? ''),
                'drawer_user_name' => (string) ($header['drawer_user_name'] ?? ''),
                'order_type_text' => $this->orderTypeText((int) $header['order_type']),
                'ship_state_text' => $this->shipStateText((int) $header['ship_state']),
                'remark' => (string) ($header['remark'] ?? ''),
            ],
            'item' => [
                'product_code' => (string) $item['product_code'],
                'product_name' => (string) $item['product_name'],
                'product_spec' => (string) $item['product_spec'],
                'unit_name' => (string) $item['unit_name'],
                'quantity' => $this->formatDecimal((string) $item['quantity']),
                'price' => $this->formatDecimal((string) $item['price']),
                'tax_price' => $this->formatDecimal((string) $item['tax_price']),
                'amount' => $this->formatDecimal((string) $item['amount']),
                'tax_amount' => $this->formatDecimal((string) $item['tax_amount']),
            ],
        ]);
    }

    protected function buildListQuery(array $payload): Query
    {
        $query = Db::name('sales_order_item')
            ->alias('soi')
            ->join('sales_order so', 'so.sales_order_id = soi.sales_order_id')
            ->where('so.del_state', 0)
            ->where('soi.del_state', 0)
            ->field([
                'soi.id',
                'soi.sales_order_item_id',
                'soi.sales_order_id',
                'so.contract_no',
                'so.customer_name',
                'so.order_date',
                'so.order_type',
                'so.ship_state',
                'so.drawer_user_name',
                'soi.product_name',
                'soi.product_spec',
                'soi.quantity',
                'soi.price',
                'soi.amount',
                'soi.remark',
            ]);

        $contractNo = trim((string) ($payload['contract_no'] ?? ''));
        $customerName = trim((string) ($payload['customer_name'] ?? ''));
        $productName = trim((string) ($payload['product_name'] ?? ''));
        $productSpec = trim((string) ($payload['product_spec'] ?? ''));
        $orderDate = trim((string) ($payload['order_date'] ?? ''));
        $orderType = $payload['order_type'] ?? '';
        $shipState = $payload['ship_state'] ?? '';

        if ($contractNo !== '') {
            $query->whereLike('so.contract_no', '%' . $contractNo . '%');
        }
        if ($customerName !== '') {
            $query->whereLike('so.customer_name', '%' . $customerName . '%');
        }
        if ($productName !== '') {
            $query->whereLike('soi.product_name', '%' . $productName . '%');
        }
        if ($productSpec !== '') {
            $query->whereLike('soi.product_spec', '%' . $productSpec . '%');
        }
        if ($orderDate !== '') {
            $query->where('so.order_date', $orderDate);
        }
        if ($orderType !== '' && $orderType !== null) {
            $query->where('so.order_type', (int) $orderType);
        }
        if ($shipState !== '' && $shipState !== null) {
            $query->where('so.ship_state', (int) $shipState);
        }

        return $query;
    }

    protected function orderTypeText(int $value): string
    {
        return $value === 2 ? '翻单' : '销售单';
    }

    protected function shipStateText(int $value): string
    {
        $mapping = [0 => '未发货', 1 => '部分发货', 2 => '全部发货'];
        return $mapping[$value] ?? '未知';
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
