<?php

return [
    // 自定义纪元时间，单位毫秒，默认 2024-01-01 00:00:00
    'epoch' => (int) env('snowflake.epoch', 1704067200000),
    // 机器 ID，取值范围 0-31
    'worker_id' => (int) env('snowflake.worker_id', 1),
    // 机房 ID，取值范围 0-31
    'datacenter_id' => (int) env('snowflake.datacenter_id', 1),
];
