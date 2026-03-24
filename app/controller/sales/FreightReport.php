<?php
declare (strict_types = 1);

namespace app\controller\sales;

use app\BaseController;
use think\exception\ValidateException;
use think\facade\Db;
use think\response\Json;

class FreightReport extends BaseController
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
                'outbound_no' => 'max:64',
                'customer_name' => 'max:128',
                'document_date' => 'dateFormat:Y-m-d',
                'page' => 'integer|egt:1',
                'page_size' => 'integer|between:1,200',
            ]);
        } catch (ValidateException $exception) {
            return $this->errorResponse($exception->getMessage());
        }

        $page = max(1, (int) ($payload['page'] ?? 1));
        $pageSize = max(1, min(200, (int) ($payload['page_size'] ?? 20)));

        $query = Db::name('sales_outbound')->where('del_state', 0);

        $outboundNo = trim((string) ($payload['outbound_no'] ?? ''));
        if ($outboundNo !== '') {
            $query->whereLike('outbound_no', '%' . $outboundNo . '%');
        }

        $customerName = trim((string) ($payload['customer_name'] ?? ''));
        if ($customerName !== '') {
            $query->whereLike('customer_name', '%' . $customerName . '%');
        }

        $documentDate = trim((string) ($payload['document_date'] ?? ''));
        if ($documentDate !== '') {
            $query->where('document_date', $documentDate);
        }

        $total = (int) (clone $query)->count();
        $rows = (clone $query)
            ->field([
                'id',
                'outbound_id',
                'customer_name',
                'outbound_no',
                'document_date',
                'express_no',
                'logistics_fee',
                'driver_name',
                'vehicle_no',
                'ship_date',
                'maker_user_name',
                'create_time',
            ])
            ->order('document_date', 'desc')
            ->order('id', 'desc')
            ->page($page, $pageSize)
            ->select()
            ->toArray();

        $list = array_map(function (array $row): array {
            return [
                'id' => (int) $row['id'],
                'outbound_id' => (int) $row['outbound_id'],
                'customer_name' => (string) $row['customer_name'],
                'outbound_no' => (string) $row['outbound_no'],
                'document_date' => (string) $row['document_date'],
                'express_no' => (string) ($row['express_no'] ?? ''),
                'logistics_fee' => (string) ($row['logistics_fee'] ?? ''),
                'driver_name' => (string) ($row['driver_name'] ?? ''),
                'vehicle_no' => (string) ($row['vehicle_no'] ?? ''),
                'ship_date' => (string) ($row['ship_date'] ?? ''),
                'maker_user_name' => (string) ($row['maker_user_name'] ?? ''),
                'create_time' => (string) ($row['create_time'] ?? ''),
            ];
        }, $rows);

        return $this->successResponse('查询成功', [
            'list' => $list,
            'total' => $total,
            'page' => $page,
            'page_size' => $pageSize,
        ]);
    }

    public function update(): Json
    {
        $authResult = $this->checkAuthorization();
        if ($authResult !== null) {
            return $authResult;
        }

        $payload = $this->getRequestData();
        try {
            $this->validate($payload, [
                'outbound_id' => 'require|integer|gt:0',
                'express_no' => 'max:128',
                'logistics_fee' => 'max:128',
                'driver_name' => 'max:64',
                'vehicle_no' => 'max:64',
            ]);
        } catch (ValidateException $exception) {
            return $this->errorResponse($exception->getMessage());
        }

        $outboundId = (int) $payload['outbound_id'];
        $exists = Db::name('sales_outbound')->where('del_state', 0)->where('outbound_id', $outboundId)->find();
        if (!$exists) {
            return $this->errorResponse('Delivery order not found');
        }

        Db::name('sales_outbound')->where('outbound_id', $outboundId)->update([
            'express_no' => (string) ($payload['express_no'] ?? ''),
            'logistics_fee' => (string) ($payload['logistics_fee'] ?? ''),
            'driver_name' => (string) ($payload['driver_name'] ?? ''),
            'vehicle_no' => (string) ($payload['vehicle_no'] ?? ''),
        ]);

        return $this->successResponse('保存成功');
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
