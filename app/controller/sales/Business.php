<?php
declare (strict_types = 1);

namespace app\controller\sales;

use app\BaseController;
use think\db\Query;
use think\exception\ValidateException;
use think\facade\Db;
use think\response\Json;

class Business extends BaseController
{
    protected $authClaims = [];

    public function bootstrap(): Json
    {
        $authResult = $this->ensureAuthorized();
        if ($authResult !== null) {
            return $authResult;
        }

        $customers = Db::name('sales_customer')
            ->where('del_state', 0)
            ->where('customer_state', 1)
            ->field(['customer_id', 'customer_name', 'tax_no', 'default_tax_rate', 'default_payment_method'])
            ->order('id', 'asc')
            ->select()
            ->toArray();

        $products = Db::name('sales_product')
            ->where('del_state', 0)
            ->where('product_state', 1)
            ->fieldRaw('product_id, product_code, product_name, product_spec, unit_name, tax_rate AS default_tax_rate, default_price, default_tax_price')
            ->order('id', 'asc')
            ->select()
            ->toArray();

        return $this->successResponse('Query success', [
            'customers' => $customers,
            'products' => $products,
            'audit_state_options' => $this->auditStateOptions(),
            'convert_state_options' => $this->convertStateOptions(),
        ]);
    }

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
                'order_date' => 'dateFormat:Y-m-d',
                'audit_state' => 'integer|in:0,1,2,3',
                'convert_state' => 'integer|in:0,1,2',
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
            ->order('bo.order_date', 'desc')
            ->order('bo.id', 'desc')
            ->page($page, $pageSize)
            ->select()
            ->toArray();

        $list = array_map(function (array $row): array {
            return [
                'id' => (int) $row['id'],
                'business_order_id' => (int) $row['business_order_id'],
                'business_order_no' => (string) $row['business_order_no'],
                'customer_name' => (string) $row['customer_name'],
                'order_date' => (string) $row['order_date'],
                'delivery_date' => (string) ($row['delivery_date'] ?? ''),
                'tax_rate' => $this->formatDecimal((string) $row['tax_rate']),
                'audit_state' => (int) $row['audit_state'],
                'audit_state_text' => $this->auditStateText((int) $row['audit_state']),
                'convert_state' => (int) $row['convert_state'],
                'convert_state_text' => $this->convertStateText((int) $row['convert_state']),
                'product_code' => (string) ($row['product_code'] ?? ''),
                'product_name' => (string) ($row['product_name'] ?? ''),
                'product_spec' => (string) ($row['product_spec'] ?? ''),
                'unit_name' => (string) ($row['unit_name'] ?? ''),
                'quantity' => $this->formatDecimal((string) ($row['quantity'] ?? '0')),
                'tax_price' => $this->formatDecimal((string) ($row['tax_price'] ?? '0')),
                'tax_amount' => $this->formatDecimal((string) ($row['tax_amount'] ?? '0')),
                'maker_user_name' => (string) ($row['maker_user_name'] ?? ''),
                'audit_user_name' => (string) ($row['audit_user_name'] ?? ''),
                'create_time' => substr((string) ($row['create_time'] ?? ''), 0, 10),
            ];
        }, $rows);

        return $this->successResponse('Query success', [
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

        $businessOrderId = (int) (($this->getRequestData())['business_order_id'] ?? 0);
        if ($businessOrderId <= 0) {
            return $this->errorResponse('business_order_id is required');
        }

        $header = Db::name('sales_business_order')
            ->where('del_state', 0)
            ->where('business_order_id', $businessOrderId)
            ->find();
        if (!$header) {
            return $this->errorResponse('Business order not found');
        }

        $items = Db::name('sales_business_order_item')
            ->where('del_state', 0)
            ->where('business_order_id', $businessOrderId)
            ->order('line_no', 'asc')
            ->select()
            ->toArray();

        return $this->successResponse('Query success', [
            'header' => [
                'business_order_id' => (int) $header['business_order_id'],
                'business_order_no' => (string) $header['business_order_no'],
                'customer_id' => (int) $header['customer_id'],
                'customer_name' => (string) $header['customer_name'],
                'order_date' => (string) $header['order_date'],
                'delivery_date' => (string) ($header['delivery_date'] ?? ''),
                'tax_rate' => $this->formatDecimal((string) $header['tax_rate']),
                'item_count' => (int) $header['item_count'],
                'total_quantity' => $this->formatDecimal((string) $header['total_quantity']),
                'total_amount' => $this->formatDecimal((string) $header['total_amount']),
                'total_tax_amount' => $this->formatDecimal((string) $header['total_tax_amount']),
                'audit_state' => (int) $header['audit_state'],
                'audit_state_text' => $this->auditStateText((int) $header['audit_state']),
                'convert_state' => (int) $header['convert_state'],
                'convert_state_text' => $this->convertStateText((int) $header['convert_state']),
                'maker_user_name' => (string) ($header['maker_user_name'] ?? ''),
                'audit_user_name' => (string) ($header['audit_user_name'] ?? ''),
                'create_time' => (string) ($header['create_time'] ?? ''),
                'audit_time' => (string) ($header['audit_time'] ?? ''),
                'remark' => (string) ($header['remark'] ?? ''),
            ],
            'items' => array_map(function (array $item): array {
                return [
                    'business_order_item_id' => (int) $item['business_order_item_id'],
                    'line_no' => (int) $item['line_no'],
                    'product_id' => (int) $item['product_id'],
                    'product_code' => (string) $item['product_code'],
                    'product_name' => (string) $item['product_name'],
                    'product_spec' => (string) $item['product_spec'],
                    'unit_name' => (string) $item['unit_name'],
                    'quantity' => $this->formatDecimal((string) $item['quantity']),
                    'tax_rate' => $this->formatDecimal((string) $item['tax_rate']),
                    'price' => $this->formatDecimal((string) $item['price']),
                    'tax_price' => $this->formatDecimal((string) $item['tax_price']),
                    'amount' => $this->formatDecimal((string) $item['amount']),
                    'tax_amount' => $this->formatDecimal((string) $item['tax_amount']),
                    'remark' => (string) ($item['remark'] ?? ''),
                ];
            }, $items),
        ]);
    }

    public function create(): Json
    {
        $authResult = $this->ensureAuthorized();
        if ($authResult !== null) {
            return $authResult;
        }

        $payload = $this->getRequestData();
        $items = is_array($payload['items'] ?? null) ? $payload['items'] : [];
        if (empty($items)) {
            return $this->errorResponse('At least one business order item is required');
        }

        try {
            $this->validate($payload, [
                'business_order_no' => 'max:64',
                'customer_id' => 'require|integer|gt:0',
                'order_date' => 'require|dateFormat:Y-m-d',
                'delivery_date' => 'dateFormat:Y-m-d',
                'tax_rate' => 'float|egt:0',
                'remark' => 'max:1024',
            ]);
        } catch (ValidateException $exception) {
            return $this->errorResponse($exception->getMessage());
        }

        $customer = Db::name('sales_customer')->where('del_state', 0)->where('customer_id', (int) $payload['customer_id'])->find();
        if (!$customer) {
            return $this->errorResponse('Customer not found');
        }

        $businessOrderNo = trim((string) ($payload['business_order_no'] ?? ''));
        if ($businessOrderNo === '') {
            $businessOrderNo = $this->generateBusinessOrderNo((string) $payload['order_date']);
        } elseif ((int) Db::name('sales_business_order')->where('business_order_no', $businessOrderNo)->where('del_state', 0)->count() > 0) {
            return $this->errorResponse('Business order number already exists');
        }

        $taxRate = (float) ($payload['tax_rate'] ?? $customer['default_tax_rate'] ?? 13);
        $deliveryDate = trim((string) ($payload['delivery_date'] ?? ''));
        $adminUserId = (int) ($this->authClaims['admin_user_id'] ?? 0);
        $username = (string) ($this->authClaims['username'] ?? 'system');

        $itemRows = [];
        $itemCount = 0;
        $totalQuantity = 0.0;
        $totalAmount = 0.0;
        $totalTaxAmount = 0.0;

        foreach ($items as $index => $item) {
            $productId = (int) ($item['product_id'] ?? 0);
            $quantity = (float) ($item['quantity'] ?? 0);
            if ($productId <= 0 || $quantity <= 0) {
                return $this->errorResponse('Invalid product or quantity in item #' . ($index + 1));
            }

            $product = Db::name('sales_product')->where('del_state', 0)->where('product_id', $productId)->find();
            if (!$product) {
                return $this->errorResponse('Product not found in item #' . ($index + 1));
            }

            $taxPrice = (float) ($item['tax_price'] ?? $product['default_tax_price'] ?? 0);
            $price = (float) ($item['price'] ?? $product['default_price'] ?? 0);
            if ($price <= 0 && $taxPrice > 0) {
                $price = round($taxPrice / (1 + ($taxRate / 100)), 4);
            }
            if ($taxPrice <= 0 && $price > 0) {
                $taxPrice = round($price * (1 + ($taxRate / 100)), 4);
            }

            $amount = round($price * $quantity, 2);
            $taxAmount = round($taxPrice * $quantity, 2);

            $itemCount++;
            $totalQuantity += $quantity;
            $totalAmount += $amount;
            $totalTaxAmount += $taxAmount;

            $itemRows[] = [
                'business_order_item_id' => (int) snowflake_id(),
                'line_no' => $index + 1,
                'product_id' => (int) $product['product_id'],
                'product_code' => (string) $product['product_code'],
                'product_name' => (string) $product['product_name'],
                'product_spec' => (string) $product['product_spec'],
                'unit_name' => (string) $product['unit_name'],
                'quantity' => $quantity,
                'tax_rate' => $taxRate,
                'price' => $price,
                'tax_price' => $taxPrice,
                'amount' => $amount,
                'tax_amount' => $taxAmount,
                'remark' => (string) ($item['remark'] ?? ''),
            ];
        }

        $businessOrderId = (int) snowflake_id();
        $now = date('Y-m-d H:i:s');

        Db::startTrans();
        try {
            Db::name('sales_business_order')->insert([
                'business_order_id' => $businessOrderId,
                'business_order_no' => $businessOrderNo,
                'customer_id' => (int) $customer['customer_id'],
                'customer_name' => (string) $customer['customer_name'],
                'order_date' => (string) $payload['order_date'],
                'delivery_date' => $deliveryDate === '' ? null : $deliveryDate,
                'tax_rate' => $taxRate,
                'item_count' => $itemCount,
                'total_quantity' => round($totalQuantity, 4),
                'total_amount' => round($totalAmount, 2),
                'total_tax_amount' => round($totalTaxAmount, 2),
                'audit_state' => 1,
                'convert_state' => 0,
                'maker_user_id' => $adminUserId,
                'maker_user_name' => $username,
                'audit_user_id' => 0,
                'audit_user_name' => '',
                'audit_time' => null,
                'source_type' => 0,
                'source_sales_order_id' => 0,
                'source_contract_no' => '',
                'remark' => (string) ($payload['remark'] ?? ''),
            ]);

            foreach ($itemRows as $itemRow) {
                Db::name('sales_business_order_item')->insert(array_merge($itemRow, [
                    'business_order_id' => $businessOrderId,
                    'business_order_no' => $businessOrderNo,
                ]));
            }

            Db::commit();
        } catch (\Throwable $exception) {
            Db::rollback();
            return $this->errorResponse('Create business order failed: ' . $exception->getMessage());
        }

        return $this->successResponse('Business order created', [
            'business_order_id' => $businessOrderId,
            'business_order_no' => $businessOrderNo,
            'create_time' => $now,
        ]);
    }

    public function batchDelete(): Json
    {
        $authResult = $this->ensureAuthorized();
        if ($authResult !== null) {
            return $authResult;
        }

        $businessOrderIds = ($this->getRequestData())['business_order_ids'] ?? [];
        if (!is_array($businessOrderIds) || empty($businessOrderIds)) {
            return $this->errorResponse('business_order_ids is required');
        }

        $businessOrderIds = array_values(array_filter(array_map('intval', $businessOrderIds), static function (int $id): bool {
            return $id > 0;
        }));
        if (empty($businessOrderIds)) {
            return $this->errorResponse('business_order_ids is required');
        }

        $orders = Db::name('sales_business_order')
            ->where('del_state', 0)
            ->whereIn('business_order_id', $businessOrderIds)
            ->field(['business_order_id', 'business_order_no', 'convert_state'])
            ->select()
            ->toArray();
        if (count($orders) !== count($businessOrderIds)) {
            return $this->errorResponse('Some business orders were not found');
        }

        foreach ($orders as $order) {
            if ((int) $order['convert_state'] !== 0) {
                return $this->errorResponse('Converted business orders cannot be deleted');
            }
        }

        $now = date('Y-m-d H:i:s');
        Db::startTrans();
        try {
            Db::name('sales_business_order')
                ->whereIn('business_order_id', $businessOrderIds)
                ->update(['del_state' => 1, 'del_time' => $now]);

            Db::name('sales_business_order_item')
                ->whereIn('business_order_id', $businessOrderIds)
                ->update(['del_state' => 1, 'del_time' => $now]);

            Db::commit();
        } catch (\Throwable $exception) {
            Db::rollback();
            return $this->errorResponse('Delete business orders failed: ' . $exception->getMessage());
        }

        return $this->successResponse('Delete success', [
            'deleted_count' => count($businessOrderIds),
        ]);
    }

    public function generateSalesOrder(): Json
    {
        $authResult = $this->ensureAuthorized();
        if ($authResult !== null) {
            return $authResult;
        }

        $businessOrderId = (int) (($this->getRequestData())['business_order_id'] ?? 0);
        if ($businessOrderId <= 0) {
            return $this->errorResponse('business_order_id is required');
        }

        $businessOrder = Db::name('sales_business_order')
            ->where('del_state', 0)
            ->where('business_order_id', $businessOrderId)
            ->find();
        if (!$businessOrder) {
            return $this->errorResponse('Business order not found');
        }
        if ((int) $businessOrder['audit_state'] !== 1) {
            return $this->errorResponse('Only approved business orders can generate sales orders');
        }
        if ((int) $businessOrder['convert_state'] !== 0) {
            return $this->errorResponse('Business order already converted');
        }

        $items = Db::name('sales_business_order_item')
            ->where('del_state', 0)
            ->where('business_order_id', $businessOrderId)
            ->order('line_no', 'asc')
            ->select()
            ->toArray();
        if (empty($items)) {
            return $this->errorResponse('Business order items not found');
        }

        $customer = Db::name('sales_customer')
            ->where('del_state', 0)
            ->where('customer_id', (int) $businessOrder['customer_id'])
            ->find();
        if (!$customer) {
            return $this->errorResponse('Customer not found');
        }

        $salesOrderId = (int) snowflake_id();
        $contractNo = $this->generateContractNo((string) $businessOrder['order_date']);
        $adminUserId = (int) ($this->authClaims['admin_user_id'] ?? 0);
        $username = (string) ($this->authClaims['username'] ?? 'system');

        Db::startTrans();
        try {
            Db::name('sales_order')->insert([
                'sales_order_id' => $salesOrderId,
                'contract_no' => $contractNo,
                'business_order_id' => (int) $businessOrder['business_order_id'],
                'business_order_no' => (string) $businessOrder['business_order_no'],
                'source_type' => 1,
                'source_sales_order_id' => 0,
                'source_contract_no' => '',
                'customer_id' => (int) $businessOrder['customer_id'],
                'customer_name' => (string) $businessOrder['customer_name'],
                'customer_tax_no' => (string) ($customer['tax_no'] ?? ''),
                'order_type' => 1,
                'order_date' => (string) $businessOrder['order_date'],
                'delivery_date' => $businessOrder['delivery_date'] ?: null,
                'audit_state' => 0,
                'order_state' => 0,
                'ship_state' => 0,
                'invoice_state' => 0,
                'payment_state' => 0,
                'reconcile_state' => 0,
                'after_sale_state' => 0,
                'current_step' => 1,
                'tax_rate' => (float) $businessOrder['tax_rate'],
                'item_count' => (int) $businessOrder['item_count'],
                'total_quantity' => (float) $businessOrder['total_quantity'],
                'total_amount' => (float) $businessOrder['total_amount'],
                'total_tax_amount' => (float) $businessOrder['total_tax_amount'],
                'discount_rate' => 0,
                'discount_amount' => 0,
                'logistics_fee' => 0,
                'other_fee' => 0,
                'shipped_quantity' => 0,
                'production_quantity' => 0,
                'reported_quantity' => 0,
                'stocked_quantity' => 0,
                'received_amount' => 0,
                'receivable_amount' => (float) $businessOrder['total_tax_amount'],
                'unpaid_amount' => (float) $businessOrder['total_tax_amount'],
                'opened_invoice_amount' => 0,
                'payment_method' => (int) ($customer['default_payment_method'] ?? 0),
                'invoice_required' => 1,
                'load_date' => null,
                'drawer_user_id' => $adminUserId,
                'drawer_user_name' => $username,
                'salesperson_user_id' => (int) ($customer['owner_user_id'] ?? $adminUserId),
                'salesperson_user_name' => (string) ($customer['owner_user_name'] ?? $username),
                'audit_user_id' => 0,
                'audit_user_name' => '',
                'audit_time' => null,
                'print_count' => 0,
                'remark' => 'Generated from business order ' . (string) $businessOrder['business_order_no'],
            ]);

            foreach ($items as $item) {
                $product = Db::name('sales_product')
                    ->where('del_state', 0)
                    ->where('product_id', (int) $item['product_id'])
                    ->find();

                Db::name('sales_order_item')->insert([
                    'sales_order_item_id' => (int) snowflake_id(),
                    'sales_order_id' => $salesOrderId,
                    'contract_no' => $contractNo,
                    'line_no' => (int) $item['line_no'],
                    'customer_id' => (int) $businessOrder['customer_id'],
                    'customer_name' => (string) $businessOrder['customer_name'],
                    'order_date' => (string) $businessOrder['order_date'],
                    'product_id' => (int) $item['product_id'],
                    'product_code' => (string) $item['product_code'],
                    'product_name' => (string) $item['product_name'],
                    'product_spec' => (string) $item['product_spec'],
                    'unit_name' => (string) $item['unit_name'],
                    'workshop_type' => (int) ($product['workshop_type'] ?? 0),
                    'quantity' => (float) $item['quantity'],
                    'production_quantity' => 0,
                    'reported_quantity' => 0,
                    'stocked_quantity' => 0,
                    'shipped_quantity' => 0,
                    'return_quantity' => 0,
                    'invoice_quantity' => 0,
                    'expected_stock_quantity' => (float) ($product['current_stock_quantity'] ?? 0),
                    'tax_rate' => (float) $item['tax_rate'],
                    'price' => (float) $item['price'],
                    'tax_price' => (float) $item['tax_price'],
                    'amount' => (float) $item['amount'],
                    'tax_amount' => (float) $item['tax_amount'],
                    'is_gift' => 0,
                    'remark' => (string) ($item['remark'] ?? ''),
                ]);
            }

            Db::name('sales_order_progress_log')->insert([
                'progress_log_id' => (int) snowflake_id(),
                'sales_order_id' => $salesOrderId,
                'contract_no' => $contractNo,
                'step_code' => 1,
                'step_name' => 'Sales',
                'step_state' => 2,
                'start_time' => date('Y-m-d H:i:s'),
                'finish_time' => date('Y-m-d H:i:s'),
                'operator_user_id' => $adminUserId,
                'operator_user_name' => $username,
                'related_no' => (string) $businessOrder['business_order_no'],
                'remark' => 'Generated from business order',
            ]);

            Db::name('sales_business_order')
                ->where('business_order_id', $businessOrderId)
                ->update([
                    'convert_state' => 2,
                    'source_sales_order_id' => $salesOrderId,
                    'source_contract_no' => $contractNo,
                ]);

            Db::commit();
        } catch (\Throwable $exception) {
            Db::rollback();
            return $this->errorResponse('Generate sales order failed: ' . $exception->getMessage());
        }

        return $this->successResponse('Generate sales order success', [
            'sales_order_id' => $salesOrderId,
            'contract_no' => $contractNo,
        ]);
    }

    protected function buildListQuery(array $payload): Query
    {
        $firstItemSubSql = Db::name('sales_business_order_item')
            ->alias('boi')
            ->where('boi.del_state', 0)
            ->fieldRaw('MIN(boi.id) AS first_id, boi.business_order_id')
            ->group('boi.business_order_id')
            ->buildSql();

        $query = Db::name('sales_business_order')
            ->alias('bo')
            ->leftJoin([$firstItemSubSql => 'first_item'], 'first_item.business_order_id = bo.business_order_id')
            ->leftJoin('sales_business_order_item boi', 'boi.id = first_item.first_id')
            ->where('bo.del_state', 0)
            ->field([
                'bo.id',
                'bo.business_order_id',
                'bo.business_order_no',
                'bo.customer_name',
                'bo.order_date',
                'bo.delivery_date',
                'bo.tax_rate',
                'bo.audit_state',
                'bo.convert_state',
                'bo.maker_user_name',
                'bo.audit_user_name',
                'bo.create_time',
                'boi.product_code',
                'boi.product_name',
                'boi.product_spec',
                'boi.unit_name',
                'boi.quantity',
                'boi.tax_price',
                'boi.tax_amount',
            ]);

        $customerName = trim((string) ($payload['customer_name'] ?? ''));
        $orderDate = trim((string) ($payload['order_date'] ?? ''));
        $auditState = $payload['audit_state'] ?? '';
        $convertState = $payload['convert_state'] ?? '';

        if ($customerName !== '') {
            $query->whereLike('bo.customer_name', '%' . $customerName . '%');
        }
        if ($orderDate !== '') {
            $query->where('bo.order_date', $orderDate);
        }
        if ($auditState !== '' && $auditState !== null) {
            $query->where('bo.audit_state', (int) $auditState);
        }
        if ($convertState !== '' && $convertState !== null) {
            $query->where('bo.convert_state', (int) $convertState);
        }

        return $query;
    }

    protected function auditStateOptions(): array
    {
        return [
            ['label' => '待审核', 'value' => 0],
            ['label' => '已审核', 'value' => 1],
            ['label' => '反审核', 'value' => 2],
            ['label' => '作废', 'value' => 3],
        ];
    }

    protected function convertStateOptions(): array
    {
        return [
            ['label' => '未转', 'value' => 0],
            ['label' => '部分转单', 'value' => 1],
            ['label' => '已转销售单', 'value' => 2],
        ];
    }

    protected function auditStateText(int $value): string
    {
        $mapping = [0 => '待审核', 1 => '已审核', 2 => '反审核', 3 => '作废'];
        return $mapping[$value] ?? '未知';
    }

    protected function convertStateText(int $value): string
    {
        $mapping = [0 => '未转', 1 => '部分转单', 2 => '已转销售单'];
        return $mapping[$value] ?? '未知';
    }

    protected function generateBusinessOrderNo(string $orderDate): string
    {
        $prefix = 'YW' . date('Ymd', strtotime($orderDate));
        $lastNo = Db::name('sales_business_order')
            ->whereLike('business_order_no', $prefix . '%')
            ->order('id', 'desc')
            ->value('business_order_no');

        $sequence = 1;
        if (is_string($lastNo) && preg_match('/(\d+)$/', $lastNo, $matches)) {
            $sequence = (int) $matches[1] + 1;
        }

        return $prefix . str_pad((string) $sequence, 3, '0', STR_PAD_LEFT);
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
