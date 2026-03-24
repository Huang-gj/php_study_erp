<?php

$deployTargetFile = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'deploy.target';
$deployTarget = is_file($deployTargetFile) ? trim((string) file_get_contents($deployTargetFile)) : env('app.deploy_target', 'server');
$deployTarget = $deployTarget === 'local' ? 'local' : 'server';
$localHost = env('app.local_host', '127.0.0.1:8080');
$serverHost = env('app.server_host', '8.133.19.244:8080');

return [
    'deploy_target'    => $deployTarget,
    'local_host'       => $localHost,
    'server_host'      => $serverHost,
    'app_host'         => $deployTarget === 'local' ? $localHost : $serverHost,
    'app_namespace'    => '',
    'with_route'       => true,
    'default_app'      => 'index',
    'default_timezone' => 'Asia/Shanghai',
    'app_map'          => [],
    'domain_bind'      => [],
    'deny_app_list'    => [],
    'exception_tmpl'   => app()->getThinkPath() . 'tpl/think_exception.tpl',
    'error_message'    => '页面错误，请稍后再试',
    'show_error_msg'   => false,
];
