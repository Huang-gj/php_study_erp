<?php
declare (strict_types = 1);

namespace app\controller\sales;

use app\BaseController;
use think\exception\ValidateException;
use think\facade\Db;
use think\response\Json;

class PriceLookup extends BaseController
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
                'customer_name' => 'max:128',
                'product_name' => 'max:128',
                'page' => 'integer|egt:1',
                'page_size' => 'integer|between:1,200',
            ]);
        } catch (ValidateException $exception) {
            return $this->errorResponse($exception->getMessage());
        }

        $page = max(1, (int) ($payload['page'] ?? 1));
        $pageSize = max(1, min(200, (int) ($payload['page_size'] ?? 20)));
        $customerName = trim((string) ($payload['customer_name'] ?? ''));
        $productName = trim((string) ($payload['product_name'] ?? ''));

        $customerQuery = Db::name('sales_customer_product_price')
            ->where('del_state', 0)
            ->field('customer_id')
            ->fieldRaw('MAX(customer_name) AS customer_name');
        if ($customerName !== '') {
            $customerQuery->whereLike('customer_name', '%' . $customerName . '%');
        }
        if ($productName !== '') {
            $customerQuery->whereLike('product_name', '%' . $productName . '%');
        }
        $customerRows = $customerQuery->group('customer_id')->order('customer_id', 'asc')->select()->toArray();

        $productBaseQuery = Db::name('sales_customer_product_price')
            ->where('del_state', 0)
            ->field('product_id')
            ->fieldRaw('MAX(product_name) AS product_name')
            ->fieldRaw('MAX(product_spec) AS product_spec');
        if ($customerName !== '') {
            $productBaseQuery->whereLike('customer_name', '%' . $customerName . '%');
        }
        if ($productName !== '') {
            $productBaseQuery->whereLike('product_name', '%' . $productName . '%');
        }

        $total = (int) (clone $productBaseQuery)->group('product_id')->count();
        $productRows = (clone $productBaseQuery)
            ->group('product_id')
            ->order('product_name', 'asc')
            ->page($page, $pageSize)
            ->select()
            ->toArray();

        $productIds = array_map(function (array $row): int {
            return (int) $row['product_id'];
        }, $productRows);

        $priceMap = [];
        if (!empty($productIds)) {
            $priceQuery = Db::name('sales_customer_product_price')
                ->where('del_state', 0)
                ->whereIn('product_id', $productIds);
            if ($customerName !== '') {
                $priceQuery->whereLike('customer_name', '%' . $customerName . '%');
            }
            if ($productName !== '') {
                $priceQuery->whereLike('product_name', '%' . $productName . '%');
            }
            $priceRows = $priceQuery->select()->toArray();
            foreach ($priceRows as $priceRow) {
                $productId = (int) $priceRow['product_id'];
                $customerId = (int) $priceRow['customer_id'];
                if (!isset($priceMap[$productId])) {
                    $priceMap[$productId] = [];
                }
                $priceMap[$productId][(string) $customerId] = $this->formatDecimal((string) $priceRow['tax_price']);
            }
        }

        $list = array_map(function (array $row) use ($priceMap): array {
            $productId = (int) $row['product_id'];
            return [
                'product_id' => $productId,
                'product_name' => (string) $row['product_name'],
                'product_spec' => (string) ($row['product_spec'] ?? ''),
                'prices' => $priceMap[$productId] ?? [],
            ];
        }, $productRows);

        $customers = array_map(function (array $row): array {
            return [
                'customer_id' => (int) $row['customer_id'],
                'customer_name' => (string) $row['customer_name'],
            ];
        }, $customerRows);

        return $this->successResponse('查询成功', [
            'customers' => $customers,
            'list' => $list,
            'total' => $total,
            'page' => $page,
            'page_size' => $pageSize,
        ]);
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
