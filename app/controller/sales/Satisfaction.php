<?php
declare (strict_types = 1);

namespace app\controller\sales;

use app\BaseController;
use think\exception\ValidateException;
use think\facade\Db;
use think\response\Json;

class Satisfaction extends BaseController
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
                'customer_name' => 'max:128',
                'page' => 'integer|egt:1',
                'page_size' => 'integer|between:1,200',
            ]);
        } catch (ValidateException $exception) {
            return $this->errorResponse($exception->getMessage());
        }

        $page = max(1, (int) ($payload['page'] ?? 1));
        $pageSize = max(1, min(200, (int) ($payload['page_size'] ?? 10)));

        $query = Db::name('sales_feedback')
            ->where('del_state', 0)
            ->where('submit_state', 1)
            ->group('customer_id, customer_name');

        $customerName = trim((string) ($payload['customer_name'] ?? ''));
        if ($customerName !== '') {
            $query->whereLike('customer_name', '%' . $customerName . '%');
        }

        $total = count((clone $query)->field(['customer_id'])->select()->toArray());
        $rows = (clone $query)
            ->fieldRaw('MIN(id) AS id')
            ->fieldRaw('customer_id')
            ->fieldRaw('customer_name')
            ->fieldRaw('COALESCE(SUM(complaint_count), 0) AS complaint_count')
            ->fieldRaw('COALESCE(AVG(score_product_quality), 0) AS score_product_quality')
            ->fieldRaw('COALESCE(AVG(score_delivery_response), 0) AS score_delivery_response')
            ->fieldRaw('COALESCE(AVG(score_pre_after_service), 0) AS score_pre_after_service')
            ->fieldRaw('COALESCE(AVG(score_price_performance), 0) AS score_price_performance')
            ->fieldRaw('COALESCE(AVG(score_customization), 0) AS score_customization')
            ->fieldRaw('COALESCE(AVG(score_cooperation_relation), 0) AS score_cooperation_relation')
            ->fieldRaw('COALESCE(AVG(overall_score), 0) AS overall_score')
            ->order('overall_score', 'desc')
            ->order('id', 'asc')
            ->page($page, $pageSize)
            ->select()
            ->toArray();

        $list = array_map(function (array $row): array {
            return [
                'id' => (int) $row['id'],
                'customer_id' => (int) $row['customer_id'],
                'customer_name' => (string) $row['customer_name'],
                'complaint_count' => (int) $row['complaint_count'],
                'scores' => [
                    'product_quality' => round((float) $row['score_product_quality'], 2),
                    'delivery_response' => round((float) $row['score_delivery_response'], 2),
                    'pre_after_service' => round((float) $row['score_pre_after_service'], 2),
                    'price_performance' => round((float) $row['score_price_performance'], 2),
                    'customization' => round((float) $row['score_customization'], 2),
                    'cooperation_relation' => round((float) $row['score_cooperation_relation'], 2),
                ],
                'overall_score' => round((float) $row['overall_score'], 2),
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
