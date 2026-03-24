<?php
declare (strict_types = 1);

namespace app\controller\sales;

use app\BaseController;
use think\exception\ValidateException;
use think\facade\Db;
use think\response\Json;

class InvoiceRecord extends BaseController
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
            ->where('invoice_required', 1)
            ->where('invoice_state', '<>', 2)
            ->order('order_date', 'desc')
            ->order('id', 'desc')
            ->limit(100)
            ->select()
            ->toArray();

        $candidateOrders = array_map(function (array $row): array {
            return [
                'sales_order_id' => (int) $row['sales_order_id'],
                'contract_no' => (string) $row['contract_no'],
                'customer_name' => (string) $row['customer_name'],
                'order_date' => (string) $row['order_date'],
                'delivery_date' => (string) ($row['delivery_date'] ?? ''),
                'total_tax_amount' => $this->formatMoney((string) $row['total_tax_amount']),
                'drawer_user_name' => (string) $row['drawer_user_name'],
            ];
        }, $rows);

        return $this->successResponse('查询成功', [
            'candidate_orders' => $candidateOrders,
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
                'invoice_no' => 'max:64',
                'customer_name' => 'max:128',
                'invoice_date' => 'dateFormat:Y-m-d',
                'page' => 'integer|egt:1',
                'page_size' => 'integer|between:1,200',
            ]);
        } catch (ValidateException $exception) {
            return $this->errorResponse($exception->getMessage());
        }

        $page = max(1, (int) ($payload['page'] ?? 1));
        $pageSize = max(1, min(200, (int) ($payload['page_size'] ?? 10)));

        $query = Db::name('sales_invoice')->where('del_state', 0);
        $invoiceNo = trim((string) ($payload['invoice_no'] ?? ''));
        $customerName = trim((string) ($payload['customer_name'] ?? ''));
        $invoiceDate = trim((string) ($payload['invoice_date'] ?? ''));

        if ($invoiceNo !== '') {
            $query->whereLike('invoice_no', '%' . $invoiceNo . '%');
        }
        if ($customerName !== '') {
            $query->whereLike('customer_name', '%' . $customerName . '%');
        }
        if ($invoiceDate !== '') {
            $query->where('invoice_date', $invoiceDate);
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
                'invoice_id' => (int) $row['invoice_id'],
                'invoice_no' => (string) $row['invoice_no'],
                'customer_name' => (string) $row['customer_name'],
                'invoice_amount' => $this->formatMoney((string) $row['invoice_amount']),
                'drawer_user_name' => (string) $row['drawer_user_name'],
                'invoice_date' => (string) $row['invoice_date'],
                'create_time' => (string) $row['create_time'],
                'audit_state' => (int) $row['audit_state'],
                'audit_state_text' => $this->mapAuditStateText((int) $row['audit_state']),
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

        $invoiceId = (int) (($this->getRequestData())['invoice_id'] ?? 0);
        if ($invoiceId <= 0) {
            return $this->errorResponse('invoice_id is required');
        }

        $invoice = Db::name('sales_invoice')
            ->where('del_state', 0)
            ->where('invoice_id', $invoiceId)
            ->find();
        if (!$invoice) {
            return $this->errorResponse('Invoice record not found');
        }

        $bindRows = Db::name('sales_invoice_bind')
            ->where('del_state', 0)
            ->where('invoice_id', $invoiceId)
            ->order('id', 'asc')
            ->select()
            ->toArray();

        $bindList = array_map(function (array $row): array {
            return [
                'invoice_bind_id' => (int) $row['invoice_bind_id'],
                'sales_order_id' => (int) $row['sales_order_id'],
                'contract_no' => (string) $row['contract_no'],
                'outbound_id' => (int) $row['outbound_id'],
                'outbound_no' => (string) $row['outbound_no'],
                'bind_amount' => $this->formatMoney((string) $row['bind_amount']),
                'remark' => (string) ($row['remark'] ?? ''),
            ];
        }, $bindRows);

        return $this->successResponse('查询成功', [
            'invoice_id' => (int) $invoice['invoice_id'],
            'invoice_no' => (string) $invoice['invoice_no'],
            'customer_name' => (string) $invoice['customer_name'],
            'buyer_tax_no' => (string) $invoice['buyer_tax_no'],
            'invoice_type_text' => (int) $invoice['invoice_type'] === 1 ? '专票' : '普票',
            'invoice_date' => (string) $invoice['invoice_date'],
            'untaxed_amount' => $this->formatMoney((string) $invoice['untaxed_amount']),
            'tax_amount' => $this->formatMoney((string) $invoice['tax_amount']),
            'invoice_amount' => $this->formatMoney((string) $invoice['invoice_amount']),
            'drawer_user_name' => (string) $invoice['drawer_user_name'],
            'audit_state' => (int) $invoice['audit_state'],
            'audit_state_text' => $this->mapAuditStateText((int) $invoice['audit_state']),
            'audit_user_name' => (string) $invoice['audit_user_name'],
            'audit_time' => (string) ($invoice['audit_time'] ?? ''),
            'create_time' => (string) $invoice['create_time'],
            'remark' => (string) ($invoice['remark'] ?? ''),
            'bind_list' => $bindList,
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

        $order = Db::name('sales_order')->where('del_state', 0)->where('sales_order_id', $salesOrderId)->find();
        if (!$order) {
            return $this->errorResponse('Sales order not found');
        }
        if ((int) $order['audit_state'] !== 1) {
            return $this->errorResponse('Approve the sales order before invoicing');
        }
        if ((int) $order['invoice_required'] !== 1) {
            return $this->errorResponse('Current sales order does not require invoicing');
        }
        if ((int) $order['invoice_state'] === 2) {
            return $this->errorResponse('Current sales order already invoiced');
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
                'remark' => 'Generated from invoice record module',
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
                'remark' => 'Generated from invoice record module',
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
            return $this->errorResponse('Create invoice record failed: ' . $exception->getMessage());
        }

        return $this->successResponse('发货开票成功', [
            'invoice_id' => $invoiceId,
            'invoice_no' => $invoiceNo,
            'outbound_no' => $outboundNo,
        ]);
    }

    public function reverseAudit(): Json
    {
        $authResult = $this->ensureAuthorized();
        if ($authResult !== null) {
            return $authResult;
        }

        $invoiceId = (int) (($this->getRequestData())['invoice_id'] ?? 0);
        if ($invoiceId <= 0) {
            return $this->errorResponse('invoice_id is required');
        }

        $invoice = Db::name('sales_invoice')->where('del_state', 0)->where('invoice_id', $invoiceId)->find();
        if (!$invoice) {
            return $this->errorResponse('Invoice record not found');
        }
        if ((int) $invoice['audit_state'] === 2) {
            return $this->errorResponse('Current invoice record already reversed');
        }

        $bindRows = Db::name('sales_invoice_bind')
            ->where('del_state', 0)
            ->where('invoice_id', $invoiceId)
            ->select()
            ->toArray();

        Db::startTrans();
        try {
            Db::name('sales_invoice')
                ->where('invoice_id', $invoiceId)
                ->update([
                    'audit_state' => 2,
                    'audit_user_id' => 0,
                    'audit_user_name' => '',
                    'audit_time' => null,
                ]);

            foreach ($bindRows as $bindRow) {
                $salesOrderId = (int) $bindRow['sales_order_id'];
                $approvedTotal = (float) Db::name('sales_invoice_bind')
                    ->alias('sib')
                    ->join('sales_invoice si', 'si.invoice_id = sib.invoice_id')
                    ->where('sib.del_state', 0)
                    ->where('si.del_state', 0)
                    ->where('si.audit_state', 1)
                    ->where('sib.sales_order_id', $salesOrderId)
                    ->sum('sib.bind_amount');

                Db::name('sales_order')
                    ->where('sales_order_id', $salesOrderId)
                    ->update([
                        'invoice_state' => $approvedTotal > 0 ? 1 : 0,
                        'opened_invoice_amount' => $approvedTotal,
                    ]);
            }

            Db::commit();
        } catch (\Throwable $exception) {
            Db::rollback();
            return $this->errorResponse('Reverse audit failed: ' . $exception->getMessage());
        }

        return $this->successResponse('反审核成功');
    }

    protected function mapAuditStateText(int $auditState): string
    {
        $mapping = [
            0 => '待审核',
            1 => '已审核',
            2 => '反审核',
        ];

        return $mapping[$auditState] ?? '未知';
    }

    protected function formatMoney(string $value): string
    {
        return number_format((float) $value, 2, '.', '');
    }

    protected function generateDocumentNo(string $prefix, string $date): string
    {
        return $prefix . date('Ymd', strtotime($date)) . substr((string) snowflake_id(), -6);
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
