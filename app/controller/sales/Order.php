<?php
declare (strict_types = 1);

namespace app\controller\sales;

use app\BaseController;
use think\db\Query;
use think\exception\ValidateException;
use think\facade\Db;
use think\response\Json;

class Order extends BaseController
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
            ->fieldRaw('product_id, product_code, product_name, product_spec, unit_name, workshop_type, tax_rate AS default_tax_rate, default_price, default_tax_price, current_stock_quantity')
            ->order('id', 'asc')
            ->select()
            ->toArray();

        return $this->successResponse('Query success', [
            'customers' => $customers,
            'products' => $products,
            'order_type_options' => $this->orderTypeOptions(),
            'ship_state_options' => $this->shipStateOptions(),
            'payment_method_options' => $this->paymentMethodOptions(),
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
        $rows = (clone $query)
            ->order('so.order_date', 'desc')
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
                'order_type' => (int) $row['order_type'],
                'order_type_text' => $this->orderTypeText((int) $row['order_type']),
                'audit_state' => (int) $row['audit_state'],
                'audit_status' => $this->auditStateText((int) $row['audit_state'], (int) $row['order_state']),
                'order_state' => (int) $row['order_state'],
                'order_state_text' => $this->orderStateText((int) $row['order_state']),
                'ship_state' => (int) $row['ship_state'],
                'ship_state_text' => $this->shipStateText((int) $row['ship_state']),
                'product_code' => (string) ($row['product_code'] ?? ''),
                'product_name' => (string) ($row['product_name'] ?? ''),
                'product_spec' => (string) ($row['product_spec'] ?? ''),
                'quantity' => $this->formatDecimal((string) ($row['quantity'] ?? '0')),
                'tax_price' => $this->formatDecimal((string) ($row['tax_price'] ?? '0')),
                'price' => $this->formatDecimal((string) ($row['price'] ?? '0')),
                'total_tax_amount' => $this->formatDecimal((string) ($row['total_tax_amount'] ?? '0')),
                'expected_stock_quantity' => $this->formatDecimal((string) ($row['expected_stock_quantity'] ?? '0')),
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

        $payload = $this->getRequestData();
        $salesOrderId = (int) ($payload['sales_order_id'] ?? 0);
        if ($salesOrderId <= 0) {
            return $this->errorResponse('sales_order_id is required');
        }

        $header = Db::name('sales_order')->where('del_state', 0)->where('sales_order_id', $salesOrderId)->find();
        if (!$header) {
            return $this->errorResponse('Sales order not found');
        }

        $items = Db::name('sales_order_item')
            ->where('del_state', 0)
            ->where('sales_order_id', $salesOrderId)
            ->field([
                'sales_order_item_id',
                'line_no',
                'product_id',
                'product_code',
                'product_name',
                'product_spec',
                'unit_name',
                'quantity',
                'price',
                'tax_price',
                'amount',
                'tax_amount',
                'expected_stock_quantity',
                'shipped_quantity',
                'remark',
            ])
            ->order('line_no', 'asc')
            ->select()
            ->toArray();

        $progressLogs = Db::name('sales_order_progress_log')
            ->where('del_state', 0)
            ->where('sales_order_id', $salesOrderId)
            ->field([
                'progress_log_id',
                'step_code',
                'step_name',
                'step_state',
                'start_time',
                'finish_time',
                'operator_user_name',
                'related_no',
                'remark',
            ])
            ->order('id', 'asc')
            ->select()
            ->toArray();

        return $this->successResponse('Query success', [
            'header' => [
                'sales_order_id' => (int) $header['sales_order_id'],
                'contract_no' => (string) $header['contract_no'],
                'customer_name' => (string) $header['customer_name'],
                'customer_tax_no' => (string) $header['customer_tax_no'],
                'order_type' => (int) $header['order_type'],
                'order_type_text' => $this->orderTypeText((int) $header['order_type']),
                'order_date' => (string) $header['order_date'],
                'delivery_date' => (string) ($header['delivery_date'] ?? ''),
                'audit_state' => (int) $header['audit_state'],
                'audit_status' => $this->auditStateText((int) $header['audit_state'], (int) $header['order_state']),
                'order_state' => (int) $header['order_state'],
                'order_state_text' => $this->orderStateText((int) $header['order_state']),
                'ship_state' => (int) $header['ship_state'],
                'ship_state_text' => $this->shipStateText((int) $header['ship_state']),
                'payment_method' => (int) $header['payment_method'],
                'payment_method_text' => $this->paymentMethodText((int) $header['payment_method']),
                'invoice_required' => (int) $header['invoice_required'],
                'total_quantity' => $this->formatDecimal((string) $header['total_quantity']),
                'total_amount' => $this->formatDecimal((string) $header['total_amount']),
                'total_tax_amount' => $this->formatDecimal((string) $header['total_tax_amount']),
                'logistics_fee' => $this->formatDecimal((string) $header['logistics_fee']),
                'other_fee' => $this->formatDecimal((string) $header['other_fee']),
                'remark' => (string) $header['remark'],
            ],
            'items' => array_map(function (array $item): array {
                $item['quantity'] = $this->formatDecimal((string) $item['quantity']);
                $item['price'] = $this->formatDecimal((string) $item['price']);
                $item['tax_price'] = $this->formatDecimal((string) $item['tax_price']);
                $item['amount'] = $this->formatDecimal((string) $item['amount']);
                $item['tax_amount'] = $this->formatDecimal((string) $item['tax_amount']);
                $item['expected_stock_quantity'] = $this->formatDecimal((string) $item['expected_stock_quantity']);
                $item['shipped_quantity'] = $this->formatDecimal((string) $item['shipped_quantity']);
                return $item;
            }, $items),
            'progress_logs' => array_map(function (array $log): array {
                $log['step_state_text'] = $this->progressStateText((int) $log['step_state']);
                return $log;
            }, $progressLogs),
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
            return $this->errorResponse('At least one order item is required');
        }

        try {
            $this->validate($payload, [
                'contract_no' => 'max:64',
                'customer_id' => 'require|integer|gt:0',
                'order_type' => 'require|integer|in:1,2',
                'order_date' => 'require|dateFormat:Y-m-d',
                'delivery_date' => 'dateFormat:Y-m-d',
                'payment_method' => 'integer|in:0,1,2,3,4,5',
                'invoice_required' => 'integer|in:0,1',
                'tax_rate' => 'float|egt:0',
                'logistics_fee' => 'float|egt:0',
                'other_fee' => 'float|egt:0',
                'remark' => 'max:1024',
            ]);
        } catch (ValidateException $exception) {
            return $this->errorResponse($exception->getMessage());
        }

        $customer = Db::name('sales_customer')->where('del_state', 0)->where('customer_id', (int) $payload['customer_id'])->find();
        if (!$customer) {
            return $this->errorResponse('Customer not found');
        }

        $adminUserId = (int) ($this->authClaims['admin_user_id'] ?? 0);
        $username = (string) ($this->authClaims['username'] ?? 'system');
        $taxRate = (float) ($payload['tax_rate'] ?? $customer['default_tax_rate'] ?? 13);
        $paymentMethod = (int) ($payload['payment_method'] ?? $customer['default_payment_method'] ?? 0);
        $invoiceRequired = (int) ($payload['invoice_required'] ?? 1);
        $contractNo = trim((string) ($payload['contract_no'] ?? ''));
        $deliveryDate = trim((string) ($payload['delivery_date'] ?? ''));

        if ($contractNo === '') {
            $contractNo = $this->generateContractNo((string) $payload['order_date']);
        } elseif ((int) Db::name('sales_order')->where('contract_no', $contractNo)->where('del_state', 0)->count() > 0) {
            return $this->errorResponse('Contract number already exists');
        }

        $itemCount = 0;
        $totalQuantity = 0.0;
        $totalAmount = 0.0;
        $totalTaxAmount = 0.0;
        $itemRows = [];

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
            $expectedStockQuantity = (float) ($item['expected_stock_quantity'] ?? $product['current_stock_quantity'] ?? 0);

            $itemCount++;
            $totalQuantity += $quantity;
            $totalAmount += $amount;
            $totalTaxAmount += $taxAmount;

            $itemRows[] = [
                'sales_order_item_id' => (int) snowflake_id(),
                'line_no' => $index + 1,
                'product_id' => $productId,
                'product_code' => (string) $product['product_code'],
                'product_name' => (string) $product['product_name'],
                'product_spec' => (string) $product['product_spec'],
                'unit_name' => (string) $product['unit_name'],
                'workshop_type' => (int) $product['workshop_type'],
                'quantity' => $quantity,
                'production_quantity' => 0,
                'reported_quantity' => 0,
                'stocked_quantity' => 0,
                'shipped_quantity' => 0,
                'return_quantity' => 0,
                'invoice_quantity' => 0,
                'expected_stock_quantity' => $expectedStockQuantity,
                'tax_rate' => $taxRate,
                'price' => $price,
                'tax_price' => $taxPrice,
                'amount' => $amount,
                'tax_amount' => $taxAmount,
                'is_gift' => 0,
                'remark' => (string) ($item['remark'] ?? ''),
            ];
        }

        $salesOrderId = (int) snowflake_id();

        Db::startTrans();
        try {
            Db::name('sales_order')->insert([
                'sales_order_id' => $salesOrderId,
                'contract_no' => $contractNo,
                'business_order_id' => 0,
                'business_order_no' => '',
                'source_type' => 0,
                'source_sales_order_id' => 0,
                'source_contract_no' => '',
                'customer_id' => (int) $customer['customer_id'],
                'customer_name' => (string) $customer['customer_name'],
                'customer_tax_no' => (string) $customer['tax_no'],
                'order_type' => (int) $payload['order_type'],
                'order_date' => (string) $payload['order_date'],
                'delivery_date' => $deliveryDate === '' ? null : $deliveryDate,
                'audit_state' => 0,
                'order_state' => 0,
                'ship_state' => 0,
                'invoice_state' => 0,
                'payment_state' => 0,
                'reconcile_state' => 0,
                'after_sale_state' => 0,
                'current_step' => 1,
                'tax_rate' => $taxRate,
                'item_count' => $itemCount,
                'total_quantity' => round($totalQuantity, 4),
                'total_amount' => round($totalAmount, 2),
                'total_tax_amount' => round($totalTaxAmount, 2),
                'discount_rate' => 0,
                'discount_amount' => 0,
                'logistics_fee' => (float) ($payload['logistics_fee'] ?? 0),
                'other_fee' => (float) ($payload['other_fee'] ?? 0),
                'shipped_quantity' => 0,
                'production_quantity' => 0,
                'reported_quantity' => 0,
                'stocked_quantity' => 0,
                'received_amount' => 0,
                'receivable_amount' => round($totalTaxAmount, 2),
                'unpaid_amount' => round($totalTaxAmount, 2),
                'opened_invoice_amount' => 0,
                'payment_method' => $paymentMethod,
                'invoice_required' => $invoiceRequired,
                'load_date' => null,
                'drawer_user_id' => $adminUserId,
                'drawer_user_name' => $username,
                'salesperson_user_id' => (int) ($customer['owner_user_id'] ?? $adminUserId),
                'salesperson_user_name' => (string) ($customer['owner_user_name'] ?? $username),
                'audit_user_id' => 0,
                'audit_user_name' => '',
                'audit_time' => null,
                'print_count' => 0,
                'remark' => (string) ($payload['remark'] ?? ''),
            ]);

            foreach ($itemRows as $itemRow) {
                Db::name('sales_order_item')->insert(array_merge($itemRow, [
                    'sales_order_id' => $salesOrderId,
                    'contract_no' => $contractNo,
                    'customer_id' => (int) $customer['customer_id'],
                    'customer_name' => (string) $customer['customer_name'],
                    'order_date' => (string) $payload['order_date'],
                ]));
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
                'related_no' => $contractNo,
                'remark' => 'Sales order created',
            ]);

            Db::commit();
        } catch (\Throwable $exception) {
            Db::rollback();
            return $this->errorResponse('Create sales order failed: ' . $exception->getMessage());
        }

        return $this->successResponse('Sales order created', [
            'sales_order_id' => $salesOrderId,
            'contract_no' => $contractNo,
        ]);
    }

    public function auditPass(): Json
    {
        $authResult = $this->ensureAuthorized();
        if ($authResult !== null) {
            return $authResult;
        }

        $salesOrderId = (int) (($this->getRequestData())['sales_order_id'] ?? 0);
        if ($salesOrderId <= 0) {
            return $this->errorResponse('sales_order_id is required');
        }

        $order = Db::name('sales_order')->where('del_state', 0)->where('sales_order_id', $salesOrderId)->find();
        if (!$order) {
            return $this->errorResponse('Sales order not found');
        }
        if ((int) $order['audit_state'] === 1) {
            return $this->errorResponse('Sales order already approved');
        }

        $adminUserId = (int) ($this->authClaims['admin_user_id'] ?? 0);
        $username = (string) ($this->authClaims['username'] ?? 'system');
        $now = date('Y-m-d H:i:s');

        Db::startTrans();
        try {
            Db::name('sales_order')->where('sales_order_id', $salesOrderId)->update([
                'audit_state' => 1,
                'order_state' => 2,
                'current_step' => 2,
                'audit_user_id' => $adminUserId,
                'audit_user_name' => $username,
                'audit_time' => $now,
            ]);

            Db::name('sales_order_progress_log')->insert([
                'progress_log_id' => (int) snowflake_id(),
                'sales_order_id' => $salesOrderId,
                'contract_no' => (string) $order['contract_no'],
                'step_code' => 2,
                'step_name' => 'Production',
                'step_state' => 1,
                'start_time' => $now,
                'finish_time' => null,
                'operator_user_id' => $adminUserId,
                'operator_user_name' => $username,
                'related_no' => (string) $order['contract_no'],
                'remark' => 'Approved and moved to production',
            ]);

            Db::commit();
        } catch (\Throwable $exception) {
            Db::rollback();
            return $this->errorResponse('Approve failed: ' . $exception->getMessage());
        }

        return $this->successResponse('Approve success');
    }

    public function shipInvoice(): Json
    {
        $authResult = $this->ensureAuthorized();
        if ($authResult !== null) {
            return $authResult;
        }

        $salesOrderId = (int) (($this->getRequestData())['sales_order_id'] ?? 0);
        if ($salesOrderId <= 0) {
            return $this->errorResponse('sales_order_id is required');
        }

        $order = Db::name('sales_order')->where('del_state', 0)->where('sales_order_id', $salesOrderId)->find();
        if (!$order) {
            return $this->errorResponse('Sales order not found');
        }
        if ((int) $order['audit_state'] !== 1) {
            return $this->errorResponse('Approve the sales order before shipping and invoicing');
        }
        if ((int) $order['ship_state'] === 2) {
            return $this->errorResponse('Sales order already shipped and invoiced');
        }

        $items = Db::name('sales_order_item')
            ->where('del_state', 0)
            ->where('sales_order_id', $salesOrderId)
            ->order('line_no', 'asc')
            ->select()
            ->toArray();
        if (empty($items)) {
            return $this->errorResponse('No shippable items found');
        }

        $adminUserId = (int) ($this->authClaims['admin_user_id'] ?? 0);
        $username = (string) ($this->authClaims['username'] ?? 'system');
        $today = date('Y-m-d');
        $now = date('Y-m-d H:i:s');
        $outboundId = (int) snowflake_id();
        $invoiceId = (int) snowflake_id();
        $outboundNo = $this->generateDocumentNo('CK', $today);
        $invoiceNo = $this->generateDocumentNo('FP', $today);

        Db::startTrans();
        try {
            Db::name('sales_outbound')->insert([
                'outbound_id' => $outboundId,
                'outbound_no' => $outboundNo,
                'sales_order_id' => $salesOrderId,
                'contract_no' => (string) $order['contract_no'],
                'customer_id' => (int) $order['customer_id'],
                'customer_name' => (string) $order['customer_name'],
                'document_date' => $today,
                'ship_date' => $today,
                'order_date' => (string) $order['order_date'],
                'audit_state' => 1,
                'invoice_required' => (int) $order['invoice_required'],
                'invoice_state' => 2,
                'total_quantity' => (float) $order['total_quantity'],
                'total_amount' => (float) $order['total_tax_amount'],
                'logistics_fee' => (float) $order['logistics_fee'],
                'maker_user_id' => $adminUserId,
                'maker_user_name' => $username,
                'audit_user_id' => $adminUserId,
                'audit_user_name' => $username,
                'audit_time' => $now,
                'print_count' => 0,
                'print_without_price_count' => 0,
                'remark' => 'Generated from sales order shipping',
            ]);

            foreach ($items as $item) {
                Db::name('sales_outbound_item')->insert([
                    'outbound_item_id' => (int) snowflake_id(),
                    'outbound_id' => $outboundId,
                    'outbound_no' => $outboundNo,
                    'sales_order_id' => $salesOrderId,
                    'sales_order_item_id' => (int) $item['sales_order_item_id'],
                    'contract_no' => (string) $order['contract_no'],
                    'line_no' => (int) $item['line_no'],
                    'product_id' => (int) $item['product_id'],
                    'product_code' => (string) $item['product_code'],
                    'product_name' => (string) $item['product_name'],
                    'product_spec' => (string) $item['product_spec'],
                    'warehouse_name' => 'Default Warehouse',
                    'unit_name' => (string) $item['unit_name'],
                    'outbound_quantity' => (float) $item['quantity'],
                    'price' => (float) $item['price'],
                    'tax_price' => (float) $item['tax_price'],
                    'amount' => (float) $item['amount'],
                    'tax_amount' => (float) $item['tax_amount'],
                    'remark' => (string) $item['remark'],
                ]);

                Db::name('sales_order_item')->where('sales_order_item_id', (int) $item['sales_order_item_id'])->update([
                    'shipped_quantity' => (float) $item['quantity'],
                    'invoice_quantity' => (float) $item['quantity'],
                ]);
            }

            Db::name('sales_invoice')->insert([
                'invoice_id' => $invoiceId,
                'invoice_no' => $invoiceNo,
                'customer_id' => (int) $order['customer_id'],
                'customer_name' => (string) $order['customer_name'],
                'buyer_tax_no' => (string) $order['customer_tax_no'],
                'invoice_type' => 1,
                'invoice_date' => $today,
                'untaxed_amount' => (float) $order['total_amount'],
                'tax_amount' => round((float) $order['total_tax_amount'] - (float) $order['total_amount'], 2),
                'invoice_amount' => (float) $order['total_tax_amount'],
                'drawer_user_id' => $adminUserId,
                'drawer_user_name' => $username,
                'audit_state' => 1,
                'audit_user_id' => $adminUserId,
                'audit_user_name' => $username,
                'audit_time' => $now,
                'remark' => 'Generated from sales order invoicing',
            ]);

            Db::name('sales_invoice_bind')->insert([
                'invoice_bind_id' => (int) snowflake_id(),
                'invoice_id' => $invoiceId,
                'invoice_no' => $invoiceNo,
                'sales_order_id' => $salesOrderId,
                'contract_no' => (string) $order['contract_no'],
                'outbound_id' => $outboundId,
                'outbound_no' => $outboundNo,
                'bind_amount' => (float) $order['total_tax_amount'],
                'remark' => 'System auto bind',
            ]);

            Db::name('sales_order')->where('sales_order_id', $salesOrderId)->update([
                'ship_state' => 2,
                'invoice_state' => 2,
                'order_state' => 7,
                'current_step' => 5,
                'shipped_quantity' => (float) $order['total_quantity'],
                'opened_invoice_amount' => (float) $order['total_tax_amount'],
                'load_date' => $today,
            ]);

            Db::name('sales_order_progress_log')->insert([
                'progress_log_id' => (int) snowflake_id(),
                'sales_order_id' => $salesOrderId,
                'contract_no' => (string) $order['contract_no'],
                'step_code' => 5,
                'step_name' => 'Shipping',
                'step_state' => 2,
                'start_time' => $now,
                'finish_time' => $now,
                'operator_user_id' => $adminUserId,
                'operator_user_name' => $username,
                'related_no' => $outboundNo,
                'remark' => 'Shipping and invoicing completed',
            ]);

            Db::commit();
        } catch (\Throwable $exception) {
            Db::rollback();
            return $this->errorResponse('Ship and invoice failed: ' . $exception->getMessage());
        }

        return $this->successResponse('Ship and invoice success', [
            'outbound_no' => $outboundNo,
            'invoice_no' => $invoiceNo,
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
                'so.order_type',
                'so.audit_state',
                'so.order_state',
                'so.ship_state',
                'so.total_tax_amount',
                'soi.product_code',
                'soi.product_name',
                'soi.product_spec',
                'soi.quantity',
                'soi.tax_price',
                'soi.price',
                'soi.expected_stock_quantity',
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
        if ($orderDate !== '') {
            $query->where('so.order_date', $orderDate);
        }
        if ($orderType !== '' && $orderType !== null) {
            $query->where('so.order_type', (int) $orderType);
        }
        if ($shipState !== '' && $shipState !== null) {
            $query->where('so.ship_state', (int) $shipState);
        }
        if ($productName !== '') {
            $query->whereIn('so.sales_order_id', Db::name('sales_order_item')->where('del_state', 0)->whereLike('product_name', '%' . $productName . '%')->field('sales_order_id'));
        }
        if ($productSpec !== '') {
            $query->whereIn('so.sales_order_id', Db::name('sales_order_item')->where('del_state', 0)->whereLike('product_spec', '%' . $productSpec . '%')->field('sales_order_id'));
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

    protected function generateDocumentNo(string $prefix, string $date): string
    {
        return $prefix . date('Ymd', strtotime($date)) . substr((string) snowflake_id(), -6);
    }

    protected function orderTypeOptions(): array
    {
        return [['label' => 'Sales Order', 'value' => 1], ['label' => 'Repeat Order', 'value' => 2]];
    }

    protected function shipStateOptions(): array
    {
        return [['label' => 'Pending', 'value' => 0], ['label' => 'Partial', 'value' => 1], ['label' => 'Shipped', 'value' => 2]];
    }

    protected function paymentMethodOptions(): array
    {
        return [
            ['label' => 'Other', 'value' => 0],
            ['label' => 'Cash', 'value' => 1],
            ['label' => 'Transfer', 'value' => 2],
            ['label' => 'WeChat', 'value' => 3],
            ['label' => 'Alipay', 'value' => 4],
            ['label' => 'Acceptance', 'value' => 5],
        ];
    }

    protected function orderTypeText(int $value): string
    {
        return $value === 2 ? 'Repeat Order' : 'Sales Order';
    }

    protected function shipStateText(int $value): string
    {
        $mapping = [0 => 'Pending', 1 => 'Partial', 2 => 'Shipped'];
        return $mapping[$value] ?? 'Unknown';
    }

    protected function paymentMethodText(int $value): string
    {
        $mapping = [0 => 'Other', 1 => 'Cash', 2 => 'Transfer', 3 => 'WeChat', 4 => 'Alipay', 5 => 'Acceptance'];
        return $mapping[$value] ?? 'Other';
    }

    protected function auditStateText(int $auditState, int $orderState): string
    {
        if ($auditState === 1) {
            return $this->orderStateText($orderState);
        }

        $mapping = [0 => 'Pending Review', 2 => 'Reversed', 3 => 'Voided'];
        return $mapping[$auditState] ?? 'Pending Review';
    }

    protected function orderStateText(int $orderState): string
    {
        $mapping = [
            0 => 'Pending Production Review',
            1 => 'Pending Sales Review',
            2 => 'In Production',
            3 => 'Pending Report',
            4 => 'Pending Stock In',
            5 => 'Waiting Outbound',
            6 => 'Pending Delivery',
            7 => 'Completed',
            8 => 'Cancelled',
        ];

        return $mapping[$orderState] ?? 'Unknown';
    }

    protected function progressStateText(int $value): string
    {
        $mapping = [0 => 'Pending', 1 => 'Processing', 2 => 'Completed'];
        return $mapping[$value] ?? 'Unknown';
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
