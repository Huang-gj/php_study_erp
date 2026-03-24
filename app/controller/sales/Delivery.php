<?php
declare (strict_types = 1);

namespace app\controller\sales;

use app\BaseController;
use think\exception\ValidateException;
use think\facade\Db;
use think\response\Json;

class Delivery extends BaseController
{
    protected $authClaims = [];

    public function bootstrap(): Json
    {
        $authResult = $this->ensureAuthorized();
        if ($authResult !== null) {
            return $authResult;
        }

        $rows = Db::name('sales_order')
            ->where('del_state', 0)
            ->where('audit_state', 1)
            ->where('ship_state', '<>', 2)
            ->order('order_date', 'desc')
            ->order('id', 'desc')
            ->limit(100)
            ->field(['sales_order_id', 'contract_no', 'customer_name', 'order_date', 'delivery_date', 'invoice_required'])
            ->select()
            ->toArray();

        return $this->successResponse('查询成功', [
            'candidate_orders' => $rows,
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
                'outbound_no' => 'max:64',
                'customer_name' => 'max:128',
                'product_name' => 'max:128',
                'product_spec' => 'max:128',
                'ship_date' => 'dateFormat:Y-m-d',
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
            ->order('so.document_date', 'desc')
            ->order('so.id', 'desc')
            ->page($page, $pageSize)
            ->select()
            ->toArray();

        $list = array_map(function (array $row): array {
            return [
                'id' => (int) $row['id'],
                'outbound_id' => (int) $row['outbound_id'],
                'outbound_no' => (string) $row['outbound_no'],
                'document_date' => (string) $row['document_date'],
                'ship_date' => (string) ($row['ship_date'] ?? ''),
                'customer_name' => (string) $row['customer_name'],
                'contract_no' => (string) $row['contract_no'],
                'product_code' => (string) ($row['product_code'] ?? ''),
                'product_name' => (string) ($row['product_name'] ?? ''),
                'product_spec' => (string) ($row['product_spec'] ?? ''),
                'unit_name' => (string) ($row['unit_name'] ?? ''),
                'outbound_quantity' => $this->formatDecimal((string) ($row['outbound_quantity'] ?? '0')),
                'invoice_required' => (int) $row['invoice_required'],
                'invoice_required_text' => (int) $row['invoice_required'] === 1 ? '是' : '否',
                'audit_state' => (int) $row['audit_state'],
                'audit_state_text' => $this->auditStateText((int) $row['audit_state']),
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
        $authResult = $this->ensureAuthorized();
        if ($authResult !== null) {
            return $authResult;
        }

        $outboundId = (int) (($this->getRequestData())['outbound_id'] ?? 0);
        if ($outboundId <= 0) {
            return $this->errorResponse('outbound_id is required');
        }

        $header = Db::name('sales_outbound')->where('del_state', 0)->where('outbound_id', $outboundId)->find();
        if (!$header) {
            return $this->errorResponse('Delivery order not found');
        }

        $items = Db::name('sales_outbound_item')
            ->where('del_state', 0)
            ->where('outbound_id', $outboundId)
            ->order('line_no', 'asc')
            ->select()
            ->toArray();

        return $this->successResponse('查询成功', [
            'header' => [
                'outbound_id' => (int) $header['outbound_id'],
                'outbound_no' => (string) $header['outbound_no'],
                'sales_order_id' => (int) $header['sales_order_id'],
                'contract_no' => (string) $header['contract_no'],
                'customer_id' => (int) $header['customer_id'],
                'customer_name' => (string) $header['customer_name'],
                'document_date' => (string) $header['document_date'],
                'ship_date' => (string) ($header['ship_date'] ?? ''),
                'order_date' => (string) ($header['order_date'] ?? ''),
                'audit_state' => (int) $header['audit_state'],
                'audit_state_text' => $this->auditStateText((int) $header['audit_state']),
                'invoice_required' => (int) $header['invoice_required'],
                'invoice_required_text' => (int) $header['invoice_required'] === 1 ? '是' : '否',
                'total_quantity' => $this->formatDecimal((string) $header['total_quantity']),
                'total_amount' => $this->formatMoney((string) $header['total_amount']),
                'logistics_fee' => $this->formatMoney((string) $header['logistics_fee']),
                'express_no' => (string) $header['express_no'],
                'driver_name' => (string) $header['driver_name'],
                'vehicle_no' => (string) $header['vehicle_no'],
                'receiver_name' => (string) $header['receiver_name'],
                'receiver_phone' => (string) $header['receiver_phone'],
                'receiver_address' => (string) $header['receiver_address'],
                'print_count' => (int) $header['print_count'],
                'print_without_price_count' => (int) $header['print_without_price_count'],
                'remark' => (string) ($header['remark'] ?? ''),
            ],
            'items' => array_map(function (array $item): array {
                return [
                    'outbound_item_id' => (int) $item['outbound_item_id'],
                    'sales_order_item_id' => (int) $item['sales_order_item_id'],
                    'line_no' => (int) $item['line_no'],
                    'product_id' => (int) $item['product_id'],
                    'product_code' => (string) $item['product_code'],
                    'product_name' => (string) $item['product_name'],
                    'product_spec' => (string) $item['product_spec'],
                    'warehouse_name' => (string) $item['warehouse_name'],
                    'unit_name' => (string) $item['unit_name'],
                    'outbound_quantity' => $this->formatDecimal((string) $item['outbound_quantity']),
                    'price' => $this->formatDecimal((string) $item['price']),
                    'tax_price' => $this->formatDecimal((string) $item['tax_price']),
                    'amount' => $this->formatMoney((string) $item['amount']),
                    'tax_amount' => $this->formatMoney((string) $item['tax_amount']),
                    'remark' => (string) ($item['remark'] ?? ''),
                ];
            }, $items),
        ]);
    }

    public function save(): Json
    {
        $authResult = $this->ensureAuthorized();
        if ($authResult !== null) {
            return $authResult;
        }

        $payload = $this->getRequestData();
        $outboundId = (int) ($payload['outbound_id'] ?? 0);
        $salesOrderId = (int) ($payload['sales_order_id'] ?? 0);
        if ($salesOrderId <= 0) {
            return $this->errorResponse('sales_order_id is required');
        }

        $order = Db::name('sales_order')->where('del_state', 0)->where('sales_order_id', $salesOrderId)->find();
        if (!$order) {
            return $this->errorResponse('Sales order not found');
        }
        $orderItems = Db::name('sales_order_item')->where('del_state', 0)->where('sales_order_id', $salesOrderId)->order('line_no', 'asc')->select()->toArray();
        if (empty($orderItems)) {
            return $this->errorResponse('Sales order items not found');
        }

        $customer = Db::name('sales_customer')->where('del_state', 0)->where('customer_id', (int) $order['customer_id'])->find();
        $inputItems = is_array($payload['items'] ?? null) ? $payload['items'] : [];
        if (empty($inputItems)) {
            return $this->errorResponse('items is required');
        }

        $itemMap = [];
        foreach ($orderItems as $orderItem) {
            $itemMap[(int) $orderItem['sales_order_item_id']] = $orderItem;
        }

        $totalQuantity = 0.0;
        $totalAmount = 0.0;
        $normalizedItems = [];
        foreach ($inputItems as $index => $inputItem) {
            $salesOrderItemId = (int) ($inputItem['sales_order_item_id'] ?? 0);
            if ($salesOrderItemId <= 0 || !isset($itemMap[$salesOrderItemId])) {
                return $this->errorResponse('Invalid sales_order_item_id');
            }
            $sourceItem = $itemMap[$salesOrderItemId];
            $quantity = (float) ($inputItem['outbound_quantity'] ?? 0);
            $price = (float) ($inputItem['price'] ?? $sourceItem['price']);
            $taxPrice = (float) ($inputItem['tax_price'] ?? $sourceItem['tax_price']);
            $amount = round($quantity * $price, 2);
            $taxAmount = round($quantity * $taxPrice, 2);
            $totalQuantity += $quantity;
            $totalAmount += $taxAmount;
            $normalizedItems[] = [
                'line_no' => $index + 1,
                'sales_order_item_id' => $salesOrderItemId,
                'product_id' => (int) $sourceItem['product_id'],
                'product_code' => (string) $sourceItem['product_code'],
                'product_name' => (string) $sourceItem['product_name'],
                'product_spec' => (string) $sourceItem['product_spec'],
                'warehouse_name' => 'Default Warehouse',
                'unit_name' => (string) $sourceItem['unit_name'],
                'outbound_quantity' => $quantity,
                'price' => $price,
                'tax_price' => $taxPrice,
                'amount' => $amount,
                'tax_amount' => $taxAmount,
                'remark' => (string) ($inputItem['remark'] ?? ''),
            ];
        }

        $adminUserId = (int) ($this->authClaims['admin_user_id'] ?? 0);
        $username = (string) ($this->authClaims['username'] ?? 'system');
        $documentDate = (string) ($payload['document_date'] ?? date('Y-m-d'));
        $shipDate = trim((string) ($payload['ship_date'] ?? ''));
        $logisticsFee = (float) ($payload['logistics_fee'] ?? 0);

        Db::startTrans();
        try {
            if ($outboundId > 0) {
                $existing = Db::name('sales_outbound')->where('del_state', 0)->where('outbound_id', $outboundId)->find();
                if (!$existing) {
                    throw new \RuntimeException('Delivery order not found');
                }
                Db::name('sales_outbound')->where('outbound_id', $outboundId)->update([
                    'document_date' => $documentDate,
                    'ship_date' => $shipDate !== '' ? $shipDate : null,
                    'invoice_required' => (int) ($payload['invoice_required'] ?? 0),
                    'total_quantity' => $totalQuantity,
                    'total_amount' => $totalAmount,
                    'logistics_fee' => $logisticsFee,
                    'express_no' => (string) ($payload['express_no'] ?? ''),
                    'driver_name' => (string) ($payload['driver_name'] ?? ''),
                    'vehicle_no' => (string) ($payload['vehicle_no'] ?? ''),
                    'receiver_name' => (string) ($payload['receiver_name'] ?? ''),
                    'receiver_phone' => (string) ($payload['receiver_phone'] ?? ''),
                    'receiver_address' => (string) ($payload['receiver_address'] ?? ''),
                    'remark' => (string) ($payload['remark'] ?? ''),
                ]);
                Db::name('sales_outbound_item')->where('outbound_id', $outboundId)->update(['del_state' => 1]);
            } else {
                $outboundId = (int) snowflake_id();
                Db::name('sales_outbound')->insert([
                    'outbound_id' => $outboundId,
                    'outbound_no' => $this->generateDocumentNo('CK', $documentDate),
                    'sales_order_id' => $salesOrderId,
                    'contract_no' => (string) $order['contract_no'],
                    'customer_id' => (int) $order['customer_id'],
                    'customer_name' => (string) $order['customer_name'],
                    'document_date' => $documentDate,
                    'ship_date' => $shipDate !== '' ? $shipDate : null,
                    'order_date' => (string) $order['order_date'],
                    'audit_state' => 0,
                    'invoice_required' => (int) ($payload['invoice_required'] ?? $order['invoice_required']),
                    'invoice_state' => 0,
                    'total_quantity' => $totalQuantity,
                    'total_amount' => $totalAmount,
                    'logistics_fee' => $logisticsFee,
                    'express_no' => (string) ($payload['express_no'] ?? ''),
                    'driver_name' => (string) ($payload['driver_name'] ?? ''),
                    'vehicle_no' => (string) ($payload['vehicle_no'] ?? ''),
                    'receiver_name' => (string) ($payload['receiver_name'] ?? ($customer['contact_name'] ?? '')),
                    'receiver_phone' => (string) ($payload['receiver_phone'] ?? ($customer['phone_number'] ?? '')),
                    'receiver_address' => (string) ($payload['receiver_address'] ?? ($customer['delivery_address'] ?? '')),
                    'maker_user_id' => $adminUserId,
                    'maker_user_name' => $username,
                    'audit_user_id' => 0,
                    'audit_user_name' => '',
                    'audit_time' => null,
                    'print_count' => 0,
                    'print_without_price_count' => 0,
                    'remark' => (string) ($payload['remark'] ?? ''),
                ]);
            }

            foreach ($normalizedItems as $normalizedItem) {
                Db::name('sales_outbound_item')->insert([
                    'outbound_item_id' => (int) snowflake_id(),
                    'outbound_id' => $outboundId,
                    'outbound_no' => Db::name('sales_outbound')->where('outbound_id', $outboundId)->value('outbound_no'),
                    'sales_order_id' => $salesOrderId,
                    'sales_order_item_id' => $normalizedItem['sales_order_item_id'],
                    'contract_no' => (string) $order['contract_no'],
                    'line_no' => $normalizedItem['line_no'],
                    'product_id' => $normalizedItem['product_id'],
                    'product_code' => $normalizedItem['product_code'],
                    'product_name' => $normalizedItem['product_name'],
                    'product_spec' => $normalizedItem['product_spec'],
                    'warehouse_name' => $normalizedItem['warehouse_name'],
                    'unit_name' => $normalizedItem['unit_name'],
                    'outbound_quantity' => $normalizedItem['outbound_quantity'],
                    'price' => $normalizedItem['price'],
                    'tax_price' => $normalizedItem['tax_price'],
                    'amount' => $normalizedItem['amount'],
                    'tax_amount' => $normalizedItem['tax_amount'],
                    'remark' => $normalizedItem['remark'],
                ]);
            }

            Db::commit();
        } catch (\Throwable $exception) {
            Db::rollback();
            return $this->errorResponse('Save delivery failed: ' . $exception->getMessage());
        }

        return $this->successResponse('保存成功', ['outbound_id' => $outboundId]);
    }

    public function auditPass(): Json
    {
        $authResult = $this->ensureAuthorized();
        if ($authResult !== null) {
            return $authResult;
        }
        $outboundId = (int) (($this->getRequestData())['outbound_id'] ?? 0);
        if ($outboundId <= 0) {
            return $this->errorResponse('outbound_id is required');
        }
        $header = Db::name('sales_outbound')->where('del_state', 0)->where('outbound_id', $outboundId)->find();
        if (!$header) {
            return $this->errorResponse('Delivery order not found');
        }

        $items = Db::name('sales_outbound_item')->where('del_state', 0)->where('outbound_id', $outboundId)->select()->toArray();
        $salesOrderId = (int) $header['sales_order_id'];
        $totalQty = 0.0;
        foreach ($items as $item) {
            $qty = (float) $item['outbound_quantity'];
            $totalQty += $qty;
            Db::name('sales_order_item')->where('sales_order_item_id', (int) $item['sales_order_item_id'])->update([
                'shipped_quantity' => $qty,
            ]);
        }

        $adminUserId = (int) ($this->authClaims['admin_user_id'] ?? 0);
        $username = (string) ($this->authClaims['username'] ?? 'system');
        $now = date('Y-m-d H:i:s');

        Db::startTrans();
        try {
            Db::name('sales_outbound')->where('outbound_id', $outboundId)->update([
                'audit_state' => 1,
                'audit_user_id' => $adminUserId,
                'audit_user_name' => $username,
                'audit_time' => $now,
                'ship_date' => $header['ship_date'] ?: date('Y-m-d'),
            ]);

            $order = Db::name('sales_order')->where('sales_order_id', $salesOrderId)->find();
            Db::name('sales_order')->where('sales_order_id', $salesOrderId)->update([
                'ship_state' => $totalQty > 0 ? 2 : 0,
                'order_state' => $totalQty > 0 ? 6 : (int) $order['order_state'],
                'current_step' => 5,
                'shipped_quantity' => $totalQty,
                'load_date' => $header['ship_date'] ?: date('Y-m-d'),
            ]);

            Db::commit();
        } catch (\Throwable $exception) {
            Db::rollback();
            return $this->errorResponse('Audit pass failed: ' . $exception->getMessage());
        }

        return $this->successResponse('审核通过成功');
    }

    public function reverseAudit(): Json
    {
        $authResult = $this->ensureAuthorized();
        if ($authResult !== null) {
            return $authResult;
        }
        $outboundId = (int) (($this->getRequestData())['outbound_id'] ?? 0);
        if ($outboundId <= 0) {
            return $this->errorResponse('outbound_id is required');
        }
        $header = Db::name('sales_outbound')->where('del_state', 0)->where('outbound_id', $outboundId)->find();
        if (!$header) {
            return $this->errorResponse('Delivery order not found');
        }
        $items = Db::name('sales_outbound_item')->where('del_state', 0)->where('outbound_id', $outboundId)->select()->toArray();

        Db::startTrans();
        try {
            Db::name('sales_outbound')->where('outbound_id', $outboundId)->update([
                'audit_state' => 2,
                'audit_user_id' => 0,
                'audit_user_name' => '',
                'audit_time' => null,
            ]);
            foreach ($items as $item) {
                Db::name('sales_order_item')->where('sales_order_item_id', (int) $item['sales_order_item_id'])->update([
                    'shipped_quantity' => 0,
                ]);
            }
            Db::name('sales_order')->where('sales_order_id', (int) $header['sales_order_id'])->update([
                'ship_state' => 0,
                'shipped_quantity' => 0,
            ]);
            Db::commit();
        } catch (\Throwable $exception) {
            Db::rollback();
            return $this->errorResponse('Reverse audit failed: ' . $exception->getMessage());
        }

        return $this->successResponse('反审核成功');
    }

    public function batchDelete(): Json
    {
        $authResult = $this->ensureAuthorized();
        if ($authResult !== null) {
            return $authResult;
        }
        $ids = $this->getRequestData()['outbound_ids'] ?? [];
        if (!is_array($ids) || empty($ids)) {
            return $this->errorResponse('outbound_ids is required');
        }
        Db::name('sales_outbound')->whereIn('outbound_id', $ids)->update(['del_state' => 1]);
        Db::name('sales_outbound_item')->whereIn('outbound_id', $ids)->update(['del_state' => 1]);
        return $this->successResponse('删除成功');
    }

    public function print(): Json
    {
        $authResult = $this->ensureAuthorized();
        if ($authResult !== null) {
            return $authResult;
        }
        $payload = $this->getRequestData();
        $outboundId = (int) ($payload['outbound_id'] ?? 0);
        $withoutPrice = (int) ($payload['without_price'] ?? 0);
        if ($outboundId <= 0) {
            return $this->errorResponse('outbound_id is required');
        }
        $field = $withoutPrice === 1 ? 'print_without_price_count' : 'print_count';
        Db::name('sales_outbound')->where('outbound_id', $outboundId)->inc($field)->update();
        return $this->successResponse('打印记录成功');
    }

    protected function buildListQuery(array $payload)
    {
        $firstItemSubSql = Db::name('sales_outbound_item')
            ->alias('soi')
            ->where('soi.del_state', 0)
            ->fieldRaw('MIN(soi.id) AS first_id, soi.outbound_id')
            ->group('soi.outbound_id')
            ->buildSql();

        $query = Db::name('sales_outbound')
            ->alias('so')
            ->leftJoin([$firstItemSubSql => 'first_item'], 'first_item.outbound_id = so.outbound_id')
            ->leftJoin('sales_outbound_item soi', 'soi.id = first_item.first_id')
            ->where('so.del_state', 0)
            ->field([
                'so.id',
                'so.outbound_id',
                'so.outbound_no',
                'so.document_date',
                'so.ship_date',
                'so.customer_name',
                'so.contract_no',
                'so.invoice_required',
                'so.audit_state',
                'soi.product_code',
                'soi.product_name',
                'soi.product_spec',
                'soi.unit_name',
                'soi.outbound_quantity',
            ]);

        $outboundNo = trim((string) ($payload['outbound_no'] ?? ''));
        $customerName = trim((string) ($payload['customer_name'] ?? ''));
        $productName = trim((string) ($payload['product_name'] ?? ''));
        $productSpec = trim((string) ($payload['product_spec'] ?? ''));
        $shipDate = trim((string) ($payload['ship_date'] ?? ''));
        $invoiceRequired = $payload['invoice_required'] ?? '';
        $auditState = $payload['audit_state'] ?? '';

        if ($outboundNo !== '') {
            $query->whereLike('so.outbound_no', '%' . $outboundNo . '%');
        }
        if ($customerName !== '') {
            $query->whereLike('so.customer_name', '%' . $customerName . '%');
        }
        if ($shipDate !== '') {
            $query->where('so.ship_date', $shipDate);
        }
        if ($invoiceRequired !== '' && $invoiceRequired !== null) {
            $query->where('so.invoice_required', (int) $invoiceRequired);
        }
        if ($auditState !== '' && $auditState !== null) {
            $query->where('so.audit_state', (int) $auditState);
        }
        if ($productName !== '') {
            $query->whereIn('so.outbound_id', Db::name('sales_outbound_item')->where('del_state', 0)->whereLike('product_name', '%' . $productName . '%')->field('outbound_id'));
        }
        if ($productSpec !== '') {
            $query->whereIn('so.outbound_id', Db::name('sales_outbound_item')->where('del_state', 0)->whereLike('product_spec', '%' . $productSpec . '%')->field('outbound_id'));
        }

        return $query;
    }

    protected function auditStateText(int $auditState): string
    {
        $mapping = [0 => '待审核', 1 => '已审核', 2 => '反审核', 3 => '作废'];
        return $mapping[$auditState] ?? '待审核';
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
        return number_format((float) $value, 2, '.', '');
    }

    protected function generateDocumentNo(string $prefix, string $date): string
    {
        return $prefix . '-' . date('Y-m-d', strtotime($date)) . '-' . substr((string) snowflake_id(), -4);
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
