<?php

$deployTargetFile = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'deploy.target';
$deployTarget = is_file($deployTargetFile) ? trim((string) file_get_contents($deployTargetFile)) : env('app.deploy_target', 'server');
$deployTarget = $deployTarget === 'local' ? 'local' : 'server';

$databaseType = env('database.type', 'mysql');
$localDatabaseConfig = [
    'hostname' => env('database.local_hostname', env('database.hostname', '127.0.0.1')),
    'database' => env('database.local_database', env('database.database', 'jykj_hgj')),
    'username' => env('database.local_username', env('database.username', 'root')),
    'password' => env('database.local_password', env('database.password', 'root')),
    'hostport' => env('database.local_hostport', env('database.hostport', '3306')),
    'charset'  => env('database.local_charset', env('database.charset', 'utf8')),
    'prefix'   => env('database.local_prefix', env('database.prefix', '')),
];
$serverDatabaseConfig = [
    'hostname' => env('database.server_hostname', env('database.hostname', '127.0.0.1')),
    'database' => env('database.server_database', env('database.database', 'jykj_hgj')),
    'username' => env('database.server_username', env('database.username', 'root')),
    'password' => env('database.server_password', env('database.password', 'root')),
    'hostport' => env('database.server_hostport', env('database.hostport', '3306')),
    'charset'  => env('database.server_charset', env('database.charset', 'utf8')),
    'prefix'   => env('database.server_prefix', env('database.prefix', '')),
];
$activeDatabaseConfig = $deployTarget === 'local' ? $localDatabaseConfig : $serverDatabaseConfig;

return [
    'default'         => env('database.driver', 'mysql'),
    'time_query_rule' => [],
    'auto_timestamp'  => true,
    'datetime_format' => 'Y-m-d H:i:s',
    'datetime_field'  => '',
    'connections'     => [
        'mysql' => [
            'type'            => $databaseType,
            'hostname'        => $activeDatabaseConfig['hostname'],
            'database'        => $activeDatabaseConfig['database'],
            'username'        => $activeDatabaseConfig['username'],
            'password'        => $activeDatabaseConfig['password'],
            'hostport'        => $activeDatabaseConfig['hostport'],
            'params'          => [],
            'charset'         => $activeDatabaseConfig['charset'],
            'prefix'          => $activeDatabaseConfig['prefix'],
            'deploy'          => 0,
            'rw_separate'     => false,
            'master_num'      => 1,
            'slave_no'        => '',
            'fields_strict'   => true,
            'break_reconnect' => false,
            'trigger_sql'     => env('app_debug', true),
            'fields_cache'    => false,
        ],
    ],
];
