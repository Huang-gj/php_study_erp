<?php
declare (strict_types = 1);

namespace app\controller\sales;

use app\BaseController;
use think\db\Query;
use think\exception\ValidateException;
use think\facade\Db;
use think\response\Json;

class Progress extends BaseController
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
            $currentStep = (int) ($row['current_step'] ?? 1);
            return [
                'id' => (int) $row['id'],
                'sales_order_id' => (int) $row['sales_order_id'],
                'contract_no' => (string) $row['contract_no'],
                'customer_name' => (string) $row['customer_name'],
                'order_date' => (string) $row['order_date'],
                'delivery_date' => (string) ($row['delivery_date'] ?? ''),
                'sales_total_price' => $this->formatDecimal((string) $row['total_tax_amount']),
                'drawer_user_name' => (string) ($row['drawer_user_name'] ?? ''),
                'remark' => (string) ($row['remark'] ?? ''),
                'audit_state' => (int) $row['audit_state'],
                'audit_status' => $this->auditStateText((int) $row['audit_state'], (int) $row['order_state']),
                'order_state' => (int) $row['order_state'],
                'order_state_text' => $this->orderStateText((int) $row['order_state']),
                'reconcile_state' => (int) $row['reconcile_state'],
                'reconcile_state_text' => $this->reconcileStateText((int) $row['reconcile_state']),
                'current_step' => $currentStep,
                'steps' => $this->buildProgressSteps($currentStep),
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

        $salesOrderId = (int) (($this->getRequestData())['sales_order_id'] ?? 0);
        if ($salesOrderId <= 0) {
            return $this->errorResponse('sales_order_id is required');
        }

        $header = Db::name('sales_order')
            ->where('del_state', 0)
            ->where('sales_order_id', $salesOrderId)
            ->find();
        if (!$header) {
            return $this->errorResponse('Sales order not found');
        }

        $logs = Db::name('sales_order_progress_log')
            ->where('del_state', 0)
            ->where('sales_order_id', $salesOrderId)
            ->order('id', 'asc')
            ->select()
            ->toArray();

        return $this->successResponse('查询成功', [
            'header' => [
                'sales_order_id' => (int) $header['sales_order_id'],
                'contract_no' => (string) $header['contract_no'],
                'customer_name' => (string) $header['customer_name'],
                'order_date' => (string) $header['order_date'],
                'delivery_date' => (string) ($header['delivery_date'] ?? ''),
                'sales_total_price' => $this->formatDecimal((string) $header['total_tax_amount']),
                'drawer_user_name' => (string) ($header['drawer_user_name'] ?? ''),
                'audit_status' => $this->auditStateText((int) $header['audit_state'], (int) $header['order_state']),
                'order_state_text' => $this->orderStateText((int) $header['order_state']),
                'reconcile_state_text' => $this->reconcileStateText((int) $header['reconcile_state']),
                'remark' => (string) ($header['remark'] ?? ''),
            ],
            'logs' => array_map(function (array $log): array {
                return [
                    'progress_log_id' => (int) $log['progress_log_id'],
                    'step_code' => (int) $log['step_code'],
                    'step_name' => (string) $log['step_name'],
                    'step_state' => (int) $log['step_state'],
                    'step_state_text' => $this->progressStateText((int) $log['step_state']),
                    'start_time' => (string) ($log['start_time'] ?? ''),
                    'finish_time' => (string) ($log['finish_time'] ?? ''),
                    'operator_user_name' => (string) ($log['operator_user_name'] ?? ''),
                    'related_no' => (string) ($log['related_no'] ?? ''),
                    'remark' => (string) ($log['remark'] ?? ''),
                ];
            }, $logs),
        ]);
    }

    protected function buildListQuery(array $payload): Query
    {
        $query = Db::name('sales_order')
            ->alias('so')
            ->where('so.del_state', 0)
            ->field([
                'so.id',
                'so.sales_order_id',
                'so.contract_no',
                'so.customer_name',
                'so.order_date',
                'so.delivery_date',
                'so.total_tax_amount',
                'so.drawer_user_name',
                'so.remark',
                'so.audit_state',
                'so.order_state',
                'so.reconcile_state',
                'so.current_step',
            ]);

        $contractNo = trim((string) ($payload['contract_no'] ?? ''));
        $customerName = trim((string) ($payload['customer_name'] ?? ''));
        $productName = trim((string) ($payload['product_name'] ?? ''));
        $productSpec = trim((string) ($payload['product_spec'] ?? ''));
        $orderDate = trim((string) ($payload['order_date'] ?? ''));

        if ($contractNo !== '') {
            $query->whereLike('so.contract_no', '%' . $contractNo . '%');
        }
        if ($customerName !== '') {
            $query->whereLike('so.customer_name', '%' . $customerName . '%');
        }
        if ($orderDate !== '') {
            $query->where('so.order_date', $orderDate);
        }
        if ($productName !== '') {
            $query->whereIn('so.sales_order_id', Db::name('sales_order_item')->where('del_state', 0)->whereLike('product_name', '%' . $productName . '%')->field('sales_order_id'));
        }
        if ($productSpec !== '') {
            $query->whereIn('so.sales_order_id', Db::name('sales_order_item')->where('del_state', 0)->whereLike('product_spec', '%' . $productSpec . '%')->field('sales_order_id'));
        }

        return $query;
    }

    protected function buildProgressSteps(int $currentStep): array
    {
        $steps = [
            1 => '销售',
            2 => '生产',
            3 => '报工',
            4 => '入库',
            5 => '发货',
        ];

        $result = [];
        foreach ($steps as $code => $label) {
            $state = 0;
            if ($code < $currentStep) {
                $state = 2;
            } elseif ($code === $currentStep) {
                $state = 2;
            }

            $result[] = [
                'step_code' => $code,
                'step_name' => $label,
                'step_label' => $label,
                'step_state' => $state,
            ];
        }

        return $result;
    }

    protected function auditStateText(int $auditState, int $orderState): string
    {
        if ($auditState === 1) {
            return $this->orderStateText($orderState);
        }

        $mapping = [0 => '待审核', 2 => '反审核', 3 => '作废'];
        return $mapping[$auditState] ?? '待审核';
    }

    protected function orderStateText(int $orderState): string
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

        return $mapping[$orderState] ?? '未知';
    }

    protected function reconcileStateText(int $value): string
    {
        return $value === 1 ? '已对账' : '未对账';
    }

    protected function progressStateText(int $value): string
    {
        $mapping = [0 => '未开始', 1 => '进行中', 2 => '已完成'];
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
