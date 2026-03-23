<?php
declare (strict_types = 1);

namespace app\controller\admin;

use app\BaseController;
use app\model\AdminUser;
use think\exception\ValidateException;
use think\facade\Db;
use think\response\Json;

class Auth extends BaseController
{
    public function login(): Json
    {
        $payload = $this->getRequestData();

        try {
            $this->validate($payload, [
                'username' => 'require|max:64',
                'password' => 'require|max:128',
                'login_ip' => 'max:64',
                'device_name' => 'max:100',
                'client_type' => 'max:50',
            ], [
                'username.require' => '请输入登录账号',
                'username.max' => '登录账号长度不能超过 64 个字符',
                'password.require' => '请输入登录密码',
                'password.max' => '登录密码长度不能超过 128 个字符',
                'login_ip.max' => '登录 IP 长度不能超过 64 个字符',
                'device_name.max' => '设备名称长度不能超过 100 个字符',
                'client_type.max' => '客户端类型长度不能超过 50 个字符',
            ]);
        } catch (ValidateException $exception) {
            return $this->errorResponse($exception->getMessage());
        }

        $username = trim((string) ($payload['username'] ?? ''));
        $password = (string) ($payload['password'] ?? '');
        $loginIp = trim((string) ($payload['login_ip'] ?? $this->request->ip()));

        /** @var AdminUser|null $admin */
        $admin = AdminUser::where('del_state', 0)
            ->where('username', $username)
            ->find();

        if ($admin === null) {
            return $this->errorResponse('账号或密码错误');
        }

        if ((int) $admin->admin_state !== 1) {
            return $this->errorResponse('当前管理员账号已被禁用');
        }

        if (!admin_verify_password($password, (string) $admin->password, (string) $admin->salt)) {
            $admin->save([
                'login_error_count' => (int) $admin->login_error_count + 1,
            ]);

            return $this->errorResponse('账号或密码错误');
        }

        $token = jwt_generate_token([
            'admin_user_id' => (int) $admin->admin_user_id,
            'username' => (string) $admin->username,
            'is_super_admin' => (int) $admin->is_super_admin,
        ]);

        Db::name('admin_user')
            ->where('id', (int) $admin->id)
            ->update([
                'login_error_count' => 0,
                'last_login_time' => date('Y-m-d H:i:s'),
                'last_login_ip' => $loginIp,
            ]);

        return $this->successResponse('登录成功', [
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => (int) (config('jwt.ttl') ?? 7200),
            'admin_info' => [
                'admin_user_id' => (int) $admin->admin_user_id,
                'username' => (string) $admin->username,
                'nickname' => (string) $admin->nickname,
                'real_name' => (string) $admin->real_name,
                'phone_number' => (string) $admin->phone_number,
                'avatar' => (string) $admin->avatar,
                'department_name' => (string) $admin->department_name,
                'role_name' => (string) $admin->role_name,
                'is_super_admin' => (int) $admin->is_super_admin,
            ],
        ]);
    }

    protected function successResponse(string $msg, array $data = []): Json
    {
        return json([
            'code' => 0,
            'msg' => $msg,
            'data' => $data,
        ]);
    }

    protected function errorResponse(string $msg, array $data = [], int $code = 1001): Json
    {
        return json([
            'code' => $code,
            'msg' => $msg,
            'data' => $data,
        ]);
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
