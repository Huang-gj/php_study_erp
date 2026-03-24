<?php
declare (strict_types = 1);

namespace app\controller\sales;

use app\BaseController;
use think\exception\ValidateException;
use think\facade\Db;
use think\response\Json;

class Complaint extends BaseController
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
                'page' => 'integer|egt:1',
                'page_size' => 'integer|between:1,200',
            ]);
        } catch (ValidateException $exception) {
            return $this->errorResponse($exception->getMessage());
        }

        $page = max(1, (int) ($payload['page'] ?? 1));
        $pageSize = max(1, min(200, (int) ($payload['page_size'] ?? 10)));

        $query = Db::name('sales_feedback')->where('del_state', 0);

        $contractNo = trim((string) ($payload['contract_no'] ?? ''));
        if ($contractNo !== '') {
            $query->whereLike('contract_no', '%' . $contractNo . '%');
        }

        $customerName = trim((string) ($payload['customer_name'] ?? ''));
        if ($customerName !== '') {
            $query->whereLike('customer_name', '%' . $customerName . '%');
        }

        $total = (int) (clone $query)->count();
        $rows = (clone $query)
            ->field([
                'id',
                'feedback_id',
                'feedback_token',
                'sales_order_id',
                'contract_no',
                'customer_name',
                'complaint_count',
                'score_product_quality',
                'score_delivery_response',
                'score_pre_after_service',
                'score_price_performance',
                'score_customization',
                'score_cooperation_relation',
                'order_date',
                'delivery_date',
                'drawer_user_name',
            ])
            ->order('order_date', 'desc')
            ->order('id', 'desc')
            ->page($page, $pageSize)
            ->select()
            ->toArray();

        $list = array_map(function (array $row): array {
            return [
                'id' => (int) $row['id'],
                'feedback_id' => (int) $row['feedback_id'],
                'sales_order_id' => (int) $row['sales_order_id'],
                'contract_no' => (string) $row['contract_no'],
                'customer_name' => (string) $row['customer_name'],
                'complaint_count' => (int) $row['complaint_count'],
                'scores' => [
                    'product_quality' => (int) $row['score_product_quality'],
                    'delivery_response' => (int) $row['score_delivery_response'],
                    'pre_after_service' => (int) $row['score_pre_after_service'],
                    'price_performance' => (int) $row['score_price_performance'],
                    'customization' => (int) $row['score_customization'],
                    'cooperation_relation' => (int) $row['score_cooperation_relation'],
                ],
                'order_date' => (string) ($row['order_date'] ?? ''),
                'delivery_date' => (string) ($row['delivery_date'] ?? ''),
                'drawer_user_name' => (string) ($row['drawer_user_name'] ?? ''),
                'feedback_token' => (string) $row['feedback_token'],
            ];
        }, $rows);

        return $this->successResponse('查询成功', [
            'list' => $list,
            'total' => $total,
            'page' => $page,
            'page_size' => $pageSize,
        ]);
    }

    protected function checkAuthorization(): ?Json
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
            jwt_parse_token($token);
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
