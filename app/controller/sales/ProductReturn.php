<?php
declare (strict_types = 1);

namespace app\controller\sales;

use app\BaseController;
use think\exception\ValidateException;
use think\facade\Db;
use think\response\Json;

class ProductReturn extends BaseController
{
    protected $authClaims = [];

    public function bootstrap(): Json
    {
        $authResult = $this->ensureAuthorized();
        if ($authResult !== null) {
            return $authResult;
        }

        $rows = Db::name('sales_outbound')
            ->where('del_state', 0)
            ->field(['outbound_id', 'outbound_no', 'customer_name', 'contract_no', 'ship_date'])
            ->order('id', 'desc')
            ->limit(100)
            ->select()
            ->toArray();

        return $this->successResponse('查询成功', [
            'candidate_outbounds' => $rows,
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
                'product_name' => 'max:128',
                'product_spec' => 'max:128',
                'actual_stockin_date' => 'dateFormat:Y-m-d',
                'remark' => 'max:1024',
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
            ->order('sr.create_time', 'desc')
            ->order('sr.id', 'desc')
            ->page($page, $pageSize)
            ->select()
            ->toArray();

        $list = array_map(function (array $row): array {
            return [
                'id' => (int) $row['id'],
                'return_id' => (int) $row['return_id'],
                'create_time' => (string) $row['create_time'],
                'return_no' => (string) $row['return_no'],
                'customer_name' => (string) $row['customer_name'],
                'return_type' => (int) $row['return_type'],
                'return_type_text' => $this->returnTypeText((int) $row['return_type']),
                'product_name' => (string) ($row['product_name'] ?? ''),
                'product_spec' => (string) ($row['product_spec'] ?? ''),
                'warehouse_name' => (string) ($row['warehouse_name'] ?? ''),
                'quantity' => $this->formatDecimal((string) ($row['quantity'] ?? '0')),
                'unit_name' => (string) ($row['unit_name'] ?? ''),
                'price' => $this->formatDecimal((string) ($row['price'] ?? '0')),
                'amount' => $this->formatMoney((string) ($row['amount'] ?? '0')),
                'total_amount' => $this->formatMoney((string) ($row['total_amount'] ?? '0')),
                'audit_state' => (int) $row['audit_state'],
                'audit_state_text' => $this->auditStateText((int) $row['audit_state']),
                'maker_user_name' => (string) $row['maker_user_name'],
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

        $returnId = (int) (($this->getRequestData())['return_id'] ?? 0);
        if ($returnId <= 0) {
            return $this->errorResponse('return_id is required');
        }

        $header = Db::name('sales_return')->where('del_state', 0)->where('return_id', $returnId)->find();
        if (!$header) {
            return $this->errorResponse('Return order not found');
        }

        $items = Db::name('sales_return_item')
            ->where('del_state', 0)
            ->where('return_id', $returnId)
            ->order('line_no', 'asc')
            ->select()
            ->toArray();

        return $this->successResponse('查询成功', [
            'header' => [
                'return_id' => (int) $header['return_id'],
                'return_no' => (string) $header['return_no'],
                'related_outbound_id' => (int) $header['related_outbound_id'],
                'related_outbound_no' => (string) $header['related_outbound_no'],
                'customer_id' => (int) $header['customer_id'],
                'customer_name' => (string) $header['customer_name'],
                'return_type' => (int) $header['return_type'],
                'return_type_text' => $this->returnTypeText((int) $header['return_type']),
                'actual_stockin_date' => (string) ($header['actual_stockin_date'] ?? ''),
                'total_quantity' => $this->formatDecimal((string) $header['total_quantity']),
                'total_amount' => $this->formatMoney((string) $header['total_amount']),
                'audit_state' => (int) $header['audit_state'],
                'audit_state_text' => $this->auditStateText((int) $header['audit_state']),
                'maker_user_name' => (string) $header['maker_user_name'],
                'audit_user_name' => (string) $header['audit_user_name'],
                'audit_time' => (string) ($header['audit_time'] ?? ''),
                'remark' => (string) ($header['remark'] ?? ''),
            ],
            'items' => array_map(function (array $item): array {
                return [
                    'return_item_id' => (int) $item['return_item_id'],
                    'sales_order_item_id' => (int) $item['sales_order_item_id'],
                    'product_code' => (string) $item['product_code'],
                    'product_name' => (string) $item['product_name'],
                    'product_spec' => (string) $item['product_spec'],
                    'warehouse_name' => (string) $item['warehouse_name'],
                    'unit_name' => (string) $item['unit_name'],
                    'quantity' => $this->formatDecimal((string) $item['quantity']),
                    'price' => $this->formatDecimal((string) $item['price']),
                    'amount' => $this->formatMoney((string) $item['amount']),
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
        $returnId = (int) ($payload['return_id'] ?? 0);
        $relatedOutboundId = (int) ($payload['related_outbound_id'] ?? 0);
        $returnType = (int) ($payload['return_type'] ?? 1);
        $items = is_array($payload['items'] ?? null) ? $payload['items'] : [];

        if ($relatedOutboundId <= 0) {
            return $this->errorResponse('related_outbound_id is required');
        }
        if (!in_array($returnType, [1, 2], true)) {
            return $this->errorResponse('return_type is invalid');
        }
        if (empty($items)) {
            return $this->errorResponse('items is required');
        }

        $outbound = Db::name('sales_outbound')->where('del_state', 0)->where('outbound_id', $relatedOutboundId)->find();
        if (!$outbound) {
            return $this->errorResponse('Related outbound not found');
        }

        $outboundItems = Db::name('sales_outbound_item')
            ->where('del_state', 0)
            ->where('outbound_id', $relatedOutboundId)
            ->order('line_no', 'asc')
            ->select()
            ->toArray();

        $itemMap = [];
        foreach ($outboundItems as $outboundItem) {
            $itemMap[(int) $outboundItem['sales_order_item_id']] = $outboundItem;
        }

        $totalQuantity = 0.0;
        $totalAmount = 0.0;
        $normalizedItems = [];
        foreach ($items as $index => $item) {
            $salesOrderItemId = (int) ($item['sales_order_item_id'] ?? 0);
            if ($salesOrderItemId <= 0 || !isset($itemMap[$salesOrderItemId])) {
                return $this->errorResponse('Invalid sales_order_item_id');
            }

            $sourceItem = $itemMap[$salesOrderItemId];
            $quantity = (float) ($item['quantity'] ?? 0);
            $price = (float) ($item['price'] ?? $sourceItem['tax_price']);
            if ($quantity <= 0) {
                return $this->errorResponse('quantity must be greater than 0');
            }

            $amount = round($quantity * $price, 2);
            $totalQuantity += $quantity;
            $totalAmount += $amount;
            $normalizedItems[] = [
                'line_no' => $index + 1,
                'sales_order_id' => (int) $sourceItem['sales_order_id'],
                'sales_order_item_id' => $salesOrderItemId,
                'product_id' => (int) $sourceItem['product_id'],
                'product_code' => (string) $sourceItem['product_code'],
                'product_name' => (string) $sourceItem['product_name'],
                'product_spec' => (string) $sourceItem['product_spec'],
                'warehouse_name' => (string) $sourceItem['warehouse_name'],
                'unit_name' => (string) $sourceItem['unit_name'],
                'quantity' => $quantity,
                'price' => $price,
                'amount' => $amount,
                'remark' => (string) ($item['remark'] ?? ''),
            ];
        }

        $adminUserId = (int) ($this->authClaims['admin_user_id'] ?? 0);
        $username = (string) ($this->authClaims['username'] ?? 'system');
        $actualStockinDate = trim((string) ($payload['actual_stockin_date'] ?? ''));
        $remark = (string) ($payload['remark'] ?? '');
        $returnNo = '';

        Db::startTrans();
        try {
            if ($returnId > 0) {
                $existing = Db::name('sales_return')->where('del_state', 0)->where('return_id', $returnId)->find();
                if (!$existing) {
                    throw new \RuntimeException('Return order not found');
                }

                $returnNo = (string) $existing['return_no'];
                Db::name('sales_return')->where('return_id', $returnId)->update([
                    'return_type' => $returnType,
                    'actual_stockin_date' => $actualStockinDate !== '' ? $actualStockinDate : null,
                    'total_quantity' => $totalQuantity,
                    'total_amount' => $totalAmount,
                    'remark' => $remark,
                ]);
                Db::name('sales_return_item')->where('return_id', $returnId)->update(['del_state' => 1]);
            } else {
                $returnId = (int) snowflake_id();
                $returnNo = $this->generateDocumentNo('TH');
                Db::name('sales_return')->insert([
                    'return_id' => $returnId,
                    'return_no' => $returnNo,
                    'related_outbound_id' => $relatedOutboundId,
                    'related_outbound_no' => (string) $outbound['outbound_no'],
                    'customer_id' => (int) $outbound['customer_id'],
                    'customer_name' => (string) $outbound['customer_name'],
                    'return_type' => $returnType,
                    'actual_stockin_date' => $actualStockinDate !== '' ? $actualStockinDate : null,
                    'total_quantity' => $totalQuantity,
                    'total_amount' => $totalAmount,
                    'audit_state' => 0,
                    'maker_user_id' => $adminUserId,
                    'maker_user_name' => $username,
                    'audit_user_id' => 0,
                    'audit_user_name' => '',
                    'audit_time' => null,
                    'remark' => $remark,
                ]);
            }

            foreach ($normalizedItems as $item) {
                Db::name('sales_return_item')->insert([
                    'return_item_id' => (int) snowflake_id(),
                    'return_id' => $returnId,
                    'return_no' => $returnNo,
                    'line_no' => $item['line_no'],
                    'sales_order_id' => $item['sales_order_id'],
                    'sales_order_item_id' => $item['sales_order_item_id'],
                    'product_id' => $item['product_id'],
                    'product_code' => $item['product_code'],
                    'product_name' => $item['product_name'],
                    'product_spec' => $item['product_spec'],
                    'warehouse_name' => $item['warehouse_name'],
                    'unit_name' => $item['unit_name'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'amount' => $item['amount'],
                    'remark' => $item['remark'],
                ]);
            }

            Db::commit();
        } catch (\Throwable $exception) {
            Db::rollback();
            return $this->errorResponse('Save product return failed: ' . $exception->getMessage());
        }

        return $this->successResponse('保存成功', ['return_id' => $returnId]);
    }

    public function auditPass(): Json
    {
        $authResult = $this->ensureAuthorized();
        if ($authResult !== null) {
            return $authResult;
        }

        $returnId = (int) (($this->getRequestData())['return_id'] ?? 0);
        if ($returnId <= 0) {
            return $this->errorResponse('return_id is required');
        }

        $adminUserId = (int) ($this->authClaims['admin_user_id'] ?? 0);
        $username = (string) ($this->authClaims['username'] ?? 'system');
        Db::name('sales_return')->where('return_id', $returnId)->update([
            'audit_state' => 1,
            'audit_user_id' => $adminUserId,
            'audit_user_name' => $username,
            'audit_time' => date('Y-m-d H:i:s'),
        ]);

        return $this->successResponse('审核通过成功');
    }

    public function reverseAudit(): Json
    {
        $authResult = $this->ensureAuthorized();
        if ($authResult !== null) {
            return $authResult;
        }

        $returnId = (int) (($this->getRequestData())['return_id'] ?? 0);
        if ($returnId <= 0) {
            return $this->errorResponse('return_id is required');
        }

        Db::name('sales_return')->where('return_id', $returnId)->update([
            'audit_state' => 2,
            'audit_user_id' => 0,
            'audit_user_name' => '',
            'audit_time' => null,
        ]);

        return $this->successResponse('反审核成功');
    }

    public function batchDelete(): Json
    {
        $authResult = $this->ensureAuthorized();
        if ($authResult !== null) {
            return $authResult;
        }

        $ids = $this->getRequestData()['return_ids'] ?? [];
        if (!is_array($ids) || empty($ids)) {
            return $this->errorResponse('return_ids is required');
        }

        Db::name('sales_return')->whereIn('return_id', $ids)->update(['del_state' => 1]);
        Db::name('sales_return_item')->whereIn('return_id', $ids)->update(['del_state' => 1]);

        return $this->successResponse('删除成功');
    }

    protected function buildListQuery(array $payload)
    {
        $firstItemSubSql = Db::name('sales_return_item')
            ->alias('sri')
            ->where('sri.del_state', 0)
            ->fieldRaw('MIN(sri.id) AS first_id, sri.return_id')
            ->group('sri.return_id')
            ->buildSql();

        $query = Db::name('sales_return')
            ->alias('sr')
            ->leftJoin([$firstItemSubSql => 'first_item'], 'first_item.return_id = sr.return_id')
            ->leftJoin('sales_return_item sri', 'sri.id = first_item.first_id')
            ->where('sr.del_state', 0)
            ->field([
                'sr.id',
                'sr.return_id',
                'sr.create_time',
                'sr.return_no',
                'sr.customer_name',
                'sr.return_type',
                'sr.total_amount',
                'sr.audit_state',
                'sr.maker_user_name',
                'sri.product_name',
                'sri.product_spec',
                'sri.warehouse_name',
                'sri.quantity',
                'sri.unit_name',
                'sri.price',
                'sri.amount',
            ]);

        $customerName = trim((string) ($payload['customer_name'] ?? ''));
        $productName = trim((string) ($payload['product_name'] ?? ''));
        $productSpec = trim((string) ($payload['product_spec'] ?? ''));
        $actualStockinDate = trim((string) ($payload['actual_stockin_date'] ?? ''));
        $remark = trim((string) ($payload['remark'] ?? ''));
        $auditState = $payload['audit_state'] ?? '';

        if ($customerName !== '') {
            $query->whereLike('sr.customer_name', '%' . $customerName . '%');
        }
        if ($actualStockinDate !== '') {
            $query->where('sr.actual_stockin_date', $actualStockinDate);
        }
        if ($remark !== '') {
            $query->whereLike('sr.remark', '%' . $remark . '%');
        }
        if ($auditState !== '' && $auditState !== null) {
            $query->where('sr.audit_state', (int) $auditState);
        }
        if ($productName !== '') {
            $query->whereIn('sr.return_id', Db::name('sales_return_item')->where('del_state', 0)->whereLike('product_name', '%' . $productName . '%')->field('return_id'));
        }
        if ($productSpec !== '') {
            $query->whereIn('sr.return_id', Db::name('sales_return_item')->where('del_state', 0)->whereLike('product_spec', '%' . $productSpec . '%')->field('return_id'));
        }

        return $query;
    }

    protected function returnTypeText(int $type): string
    {
        return $type === 2 ? '出库退货' : '入库退货';
    }

    protected function auditStateText(int $auditState): string
    {
        $mapping = [0 => '待审核', 1 => '已审核', 2 => '反审核'];
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

    protected function generateDocumentNo(string $prefix): string
    {
        return $prefix . '_' . substr((string) snowflake_id(), -13);
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
