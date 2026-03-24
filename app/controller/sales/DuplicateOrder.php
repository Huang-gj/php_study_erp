<?php
declare (strict_types = 1);

namespace app\controller\sales;

use app\BaseController;
use think\db\Query;
use think\exception\ValidateException;
use think\facade\Db;
use think\response\Json;

class DuplicateOrder extends BaseController
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
                'contract_no' => 'max:64',
                'customer_name' => 'max:128',
                'create_date' => 'dateFormat:Y-m-d',
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
        $rows = (clone $query)
            ->order('so.id', 'desc')
            ->page($page, $pageSize)
            ->select()
            ->toArray();

        $list = array_map(function (array $row): array {
            return [
                'id' => (int) $row['id'],
                'sales_order_id' => (int) $row['sales_order_id'],
                'contract_no' => (string) $row['contract_no'],
                'customer_name' => (string) $row['customer_name'],
                'order_date' => (string) $row['order_date'],
                'delivery_date' => (string) ($row['delivery_date'] ?? ''),
                'product_name' => (string) ($row['product_name'] ?? ''),
                'product_spec' => (string) ($row['product_spec'] ?? '--'),
                'product_quantity' => $this->formatDecimal((string) ($row['quantity'] ?? '0')),
                'price' => $this->formatDecimal((string) ($row['price'] ?? '0')),
                'sales_total_price' => $this->formatDecimal((string) ($row['total_tax_amount'] ?? '0')),
                'logistics_fee' => $this->formatDecimal((string) ($row['logistics_fee'] ?? '0')),
                'discount_rate' => $this->formatDecimal((string) ($row['discount_rate'] ?? '0')),
                'invoice_required' => (int) $row['invoice_required'],
                'invoice_required_text' => (int) $row['invoice_required'] === 1 ? '是' : '否',
                'customer_tax_no' => (string) ($row['customer_tax_no'] ?? '--'),
                'remark' => (string) ($row['remark'] ?? '--'),
            ];
        }, $rows);

        return $this->successResponse('查询成功', [
            'list' => $list,
            'total' => $total,
            'page' => $page,
            'page_size' => $pageSize,
        ]);
    }

    public function create(): Json
    {
        $authResult = $this->ensureAuthorized();
        if ($authResult !== null) {
            return $authResult;
        }

        $salesOrderId = (int) (($this->getRequestData())['sales_order_id'] ?? 0);
        if ($salesOrderId <= 0) {
            return $this->errorResponse('sales_order_id is required');
        }

        $sourceOrder = Db::name('sales_order')
            ->where('del_state', 0)
            ->where('sales_order_id', $salesOrderId)
            ->find();
        if (!$sourceOrder) {
            return $this->errorResponse('Sales order not found');
        }

        $sourceItems = Db::name('sales_order_item')
            ->where('del_state', 0)
            ->where('sales_order_id', $salesOrderId)
            ->order('line_no', 'asc')
            ->select()
            ->toArray();
        if (empty($sourceItems)) {
            return $this->errorResponse('Sales order items not found');
        }

        $newSalesOrderId = (int) snowflake_id();
        $today = date('Y-m-d');
        $contractNo = $this->generateContractNo($today);
        $adminUserId = (int) ($this->authClaims['admin_user_id'] ?? 0);
        $username = (string) ($this->authClaims['username'] ?? 'system');

        Db::startTrans();
        try {
            Db::name('sales_order')->insert([
                'sales_order_id' => $newSalesOrderId,
                'contract_no' => $contractNo,
                'business_order_id' => (int) ($sourceOrder['business_order_id'] ?? 0),
                'business_order_no' => (string) ($sourceOrder['business_order_no'] ?? ''),
                'source_type' => 2,
                'source_sales_order_id' => (int) $sourceOrder['sales_order_id'],
                'source_contract_no' => (string) $sourceOrder['contract_no'],
                'customer_id' => (int) $sourceOrder['customer_id'],
                'customer_name' => (string) $sourceOrder['customer_name'],
                'customer_tax_no' => (string) $sourceOrder['customer_tax_no'],
                'order_type' => 2,
                'order_date' => $today,
                'delivery_date' => $sourceOrder['delivery_date'] ?: null,
                'audit_state' => 0,
                'order_state' => 0,
                'ship_state' => 0,
                'invoice_state' => 0,
                'payment_state' => 0,
                'reconcile_state' => 0,
                'after_sale_state' => 0,
                'current_step' => 1,
                'tax_rate' => (float) $sourceOrder['tax_rate'],
                'item_count' => (int) $sourceOrder['item_count'],
                'total_quantity' => (float) $sourceOrder['total_quantity'],
                'total_amount' => (float) $sourceOrder['total_amount'],
                'total_tax_amount' => (float) $sourceOrder['total_tax_amount'],
                'discount_rate' => (float) $sourceOrder['discount_rate'],
                'discount_amount' => (float) $sourceOrder['discount_amount'],
                'logistics_fee' => (float) $sourceOrder['logistics_fee'],
                'other_fee' => (float) $sourceOrder['other_fee'],
                'shipped_quantity' => 0,
                'production_quantity' => 0,
                'reported_quantity' => 0,
                'stocked_quantity' => 0,
                'received_amount' => 0,
                'receivable_amount' => (float) $sourceOrder['total_tax_amount'],
                'unpaid_amount' => (float) $sourceOrder['total_tax_amount'],
                'opened_invoice_amount' => 0,
                'payment_method' => (int) $sourceOrder['payment_method'],
                'invoice_required' => (int) $sourceOrder['invoice_required'],
                'load_date' => null,
                'drawer_user_id' => $adminUserId,
                'drawer_user_name' => $username,
                'salesperson_user_id' => (int) $sourceOrder['salesperson_user_id'],
                'salesperson_user_name' => (string) $sourceOrder['salesperson_user_name'],
                'audit_user_id' => 0,
                'audit_user_name' => '',
                'audit_time' => null,
                'print_count' => 0,
                'remark' => 'Duplicated from ' . (string) $sourceOrder['contract_no'],
            ]);

            foreach ($sourceItems as $item) {
                Db::name('sales_order_item')->insert([
                    'sales_order_item_id' => (int) snowflake_id(),
                    'sales_order_id' => $newSalesOrderId,
                    'contract_no' => $contractNo,
                    'line_no' => (int) $item['line_no'],
                    'customer_id' => (int) $sourceOrder['customer_id'],
                    'customer_name' => (string) $sourceOrder['customer_name'],
                    'order_date' => $today,
                    'product_id' => (int) $item['product_id'],
                    'product_code' => (string) $item['product_code'],
                    'product_name' => (string) $item['product_name'],
                    'product_spec' => (string) $item['product_spec'],
                    'unit_name' => (string) $item['unit_name'],
                    'workshop_type' => (int) $item['workshop_type'],
                    'quantity' => (float) $item['quantity'],
                    'production_quantity' => 0,
                    'reported_quantity' => 0,
                    'stocked_quantity' => 0,
                    'shipped_quantity' => 0,
                    'return_quantity' => 0,
                    'invoice_quantity' => 0,
                    'expected_stock_quantity' => (float) $item['expected_stock_quantity'],
                    'tax_rate' => (float) $item['tax_rate'],
                    'price' => (float) $item['price'],
                    'tax_price' => (float) $item['tax_price'],
                    'amount' => (float) $item['amount'],
                    'tax_amount' => (float) $item['tax_amount'],
                    'is_gift' => (int) $item['is_gift'],
                    'remark' => (string) $item['remark'],
                ]);
            }

            Db::name('sales_order_progress_log')->insert([
                'progress_log_id' => (int) snowflake_id(),
                'sales_order_id' => $newSalesOrderId,
                'contract_no' => $contractNo,
                'step_code' => 1,
                'step_name' => 'Sales',
                'step_state' => 2,
                'start_time' => date('Y-m-d H:i:s'),
                'finish_time' => date('Y-m-d H:i:s'),
                'operator_user_id' => $adminUserId,
                'operator_user_name' => $username,
                'related_no' => (string) $sourceOrder['contract_no'],
                'remark' => 'Duplicate order created from source sales order',
            ]);

            Db::commit();
        } catch (\Throwable $exception) {
            Db::rollback();
            return $this->errorResponse('Create duplicate order failed: ' . $exception->getMessage());
        }

        return $this->successResponse('翻单成功', [
            'sales_order_id' => $newSalesOrderId,
            'contract_no' => $contractNo,
        ]);
    }

    protected function buildListQuery(array $payload): Query
    {
        $firstItemSubSql = Db::name('sales_order_item')
            ->alias('soi')
            ->where('soi.del_state', 0)
            ->fieldRaw('MIN(soi.id) AS first_id, soi.sales_order_id')
            ->group('soi.sales_order_id')
            ->buildSql();

        $query = Db::name('sales_order')
            ->alias('so')
            ->leftJoin([$firstItemSubSql => 'first_item'], 'first_item.sales_order_id = so.sales_order_id')
            ->leftJoin('sales_order_item soi', 'soi.id = first_item.first_id')
            ->where('so.del_state', 0)
            ->field([
                'so.id',
                'so.sales_order_id',
                'so.contract_no',
                'so.customer_name',
                'so.order_date',
                'so.delivery_date',
                'so.customer_tax_no',
                'so.total_tax_amount',
                'so.logistics_fee',
                'so.discount_rate',
                'so.invoice_required',
                'so.remark',
                'soi.product_name',
                'soi.product_spec',
                'soi.quantity',
                'soi.price',
            ]);

        $contractNo = trim((string) ($payload['contract_no'] ?? ''));
        $customerName = trim((string) ($payload['customer_name'] ?? ''));
        $createDate = trim((string) ($payload['create_date'] ?? ''));

        if ($contractNo !== '') {
            $query->whereLike('so.contract_no', '%' . $contractNo . '%');
        }
        if ($customerName !== '') {
            $query->whereLike('so.customer_name', '%' . $customerName . '%');
        }
        if ($createDate !== '') {
            $query->whereRaw('DATE(so.create_time) = ?', [$createDate]);
        }

        return $query;
    }

    protected function generateContractNo(string $orderDate): string
    {
        $datePart = date('Y-m', strtotime($orderDate));
        $prefix = 'SO-' . $datePart . '-';
        $lastContractNo = Db::name('sales_order')->whereLike('contract_no', $prefix . '%')->order('id', 'desc')->value('contract_no');
        $sequence = 1;
        if (is_string($lastContractNo) && preg_match('/(\d+)$/', $lastContractNo, $matches)) {
            $sequence = (int) $matches[1] + 1;
        }

        return $prefix . str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
    }

    protected function formatDecimal(string $value): string
    {
        if ($value === '') {
            return '';
        }
        $formatted = rtrim(rtrim($value, '0'), '.');
        return $formatted === '' ? '0' : $formatted;
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
