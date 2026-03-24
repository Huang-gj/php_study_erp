
SET NAMES utf8mb4;

DROP TABLE IF EXISTS `production_process`;
CREATE TABLE `production_process`
(
    id                       INT            NOT NULL AUTO_INCREMENT PRIMARY KEY,
    del_state                INT            NOT NULL DEFAULT 0 COMMENT '0-正常 1-删除',
    del_time                 TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '删除时间',
    create_time              TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    process_id               BIGINT         NOT NULL COMMENT '分布式唯一ID',
    process_code             VARCHAR(64)    NOT NULL COMMENT '工序编码',
    process_name             VARCHAR(128)   NOT NULL DEFAULT '' COMMENT '工序名称',
    process_type             TINYINT        NOT NULL DEFAULT 0 COMMENT '工序类型 0-普通 1-调漆 2-灌装 3-包装 4-质检 5-其他',
    workshop_type            TINYINT        NOT NULL DEFAULT 0 COMMENT '车间分类 0-未分类 1-水性车间 2-油性车间 3-其他',
    report_unit_name         VARCHAR(32)    NOT NULL DEFAULT '' COMMENT '报工单位',
    standard_capacity        DECIMAL(18, 4) NOT NULL DEFAULT 0.0000 COMMENT '标准产能',
    standard_minutes         DECIMAL(18, 2) NOT NULL DEFAULT 0.00 COMMENT '标准工时(分钟)',
    need_machine             TINYINT        NOT NULL DEFAULT 1 COMMENT '是否需要机台 0-否 1-是',
    need_worker              TINYINT        NOT NULL DEFAULT 1 COMMENT '是否需要工人 0-否 1-是',
    process_state            TINYINT        NOT NULL DEFAULT 1 COMMENT '状态 0-停用 1-启用',
    sort_no                  INT            NOT NULL DEFAULT 0 COMMENT '排序值',
    remark                   VARCHAR(1024)  NOT NULL DEFAULT '' COMMENT '备注',
    UNIQUE KEY uk_process_id (`process_id`),
    UNIQUE KEY uk_process_code (`process_code`),
    KEY idx_process_name (`process_name`),
    KEY idx_workshop_type (`workshop_type`),
    KEY idx_process_state (`process_state`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_general_ci
  ROW_FORMAT = Dynamic
  COMMENT ='工序主表';

DROP TABLE IF EXISTS `production_team`;
CREATE TABLE `production_team`
(
    id                       INT            NOT NULL AUTO_INCREMENT PRIMARY KEY,
    del_state                INT            NOT NULL DEFAULT 0 COMMENT '0-正常 1-删除',
    del_time                 TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '删除时间',
    create_time              TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    team_id                  BIGINT         NOT NULL COMMENT '分布式唯一ID',
    team_code                VARCHAR(64)    NOT NULL COMMENT '班组编码',
    team_name                VARCHAR(128)   NOT NULL DEFAULT '' COMMENT '班组名称',
    shift_type               TINYINT        NOT NULL DEFAULT 0 COMMENT '班次 0-普通班 1-白班 2-中班 3-晚班',
    department_id            BIGINT         NOT NULL DEFAULT 0 COMMENT '所属部门ID',
    department_name          VARCHAR(128)   NOT NULL DEFAULT '' COMMENT '所属部门名称',
    leader_user_id           BIGINT         NOT NULL DEFAULT 0 COMMENT '班组长用户ID',
    leader_user_name         VARCHAR(64)    NOT NULL DEFAULT '' COMMENT '班组长用户名',
    team_state               TINYINT        NOT NULL DEFAULT 1 COMMENT '状态 0-停用 1-启用',
    remark                   VARCHAR(1024)  NOT NULL DEFAULT '' COMMENT '备注',
    UNIQUE KEY uk_team_id (`team_id`),
    UNIQUE KEY uk_team_code (`team_code`),
    KEY idx_team_name (`team_name`),
    KEY idx_department_id (`department_id`),
    KEY idx_team_state (`team_state`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_general_ci
  ROW_FORMAT = Dynamic
  COMMENT ='生产班组表';

DROP TABLE IF EXISTS `production_machine`;
CREATE TABLE `production_machine`
(
    id                       INT            NOT NULL AUTO_INCREMENT PRIMARY KEY,
    del_state                INT            NOT NULL DEFAULT 0 COMMENT '0-正常 1-删除',
    del_time                 TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '删除时间',
    create_time              TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    machine_id               BIGINT         NOT NULL COMMENT '分布式唯一ID',
    machine_code             VARCHAR(64)    NOT NULL COMMENT '机台编码',
    machine_name             VARCHAR(128)   NOT NULL DEFAULT '' COMMENT '机台名称',
    process_id               BIGINT         NOT NULL DEFAULT 0 COMMENT '默认工序ID',
    process_name             VARCHAR(128)   NOT NULL DEFAULT '' COMMENT '默认工序名称',
    workshop_type            TINYINT        NOT NULL DEFAULT 0 COMMENT '车间分类 0-未分类 1-水性车间 2-油性车间 3-其他',
    department_id            BIGINT         NOT NULL DEFAULT 0 COMMENT '所属部门ID',
    department_name          VARCHAR(128)   NOT NULL DEFAULT '' COMMENT '所属部门名称',
    location_name            VARCHAR(128)   NOT NULL DEFAULT '' COMMENT '设备位置',
    capacity_per_day         DECIMAL(18, 4) NOT NULL DEFAULT 0.0000 COMMENT '日产能',
    machine_state            TINYINT        NOT NULL DEFAULT 1 COMMENT '状态 0-停用 1-启用 2-维修',
    remark                   VARCHAR(1024)  NOT NULL DEFAULT '' COMMENT '备注',
    UNIQUE KEY uk_machine_id (`machine_id`),
    UNIQUE KEY uk_machine_code (`machine_code`),
    KEY idx_machine_name (`machine_name`),
    KEY idx_process_id (`process_id`),
    KEY idx_workshop_type (`workshop_type`),
    KEY idx_machine_state (`machine_state`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_general_ci
  ROW_FORMAT = Dynamic
  COMMENT ='机台/设备主表';

DROP TABLE IF EXISTS `production_worker`;
CREATE TABLE `production_worker`
(
    id                       INT            NOT NULL AUTO_INCREMENT PRIMARY KEY,
    del_state                INT            NOT NULL DEFAULT 0 COMMENT '0-正常 1-删除',
    del_time                 TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '删除时间',
    create_time              TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    worker_id                BIGINT         NOT NULL COMMENT '分布式唯一ID',
    user_id                  BIGINT         NOT NULL DEFAULT 0 COMMENT '关联用户ID',
    user_name                VARCHAR(64)    NOT NULL DEFAULT '' COMMENT '用户名',
    worker_name              VARCHAR(64)    NOT NULL DEFAULT '' COMMENT '工人姓名',
    phone_number             VARCHAR(20)    NOT NULL DEFAULT '' COMMENT '手机号',
    department_id            BIGINT         NOT NULL DEFAULT 0 COMMENT '所属部门ID',
    department_name          VARCHAR(128)   NOT NULL DEFAULT '' COMMENT '所属部门名称',
    team_id                  BIGINT         NOT NULL DEFAULT 0 COMMENT '所属班组ID',
    team_name                VARCHAR(128)   NOT NULL DEFAULT '' COMMENT '所属班组名称',
    default_machine_id       BIGINT         NOT NULL DEFAULT 0 COMMENT '默认机台ID',
    default_machine_name     VARCHAR(128)   NOT NULL DEFAULT '' COMMENT '默认机台名称',
    entry_date               DATE                    DEFAULT NULL COMMENT '入职日期',
    worker_state             TINYINT        NOT NULL DEFAULT 1 COMMENT '状态 0-停用 1-启用',
    remark                   VARCHAR(1024)  NOT NULL DEFAULT '' COMMENT '备注',
    UNIQUE KEY uk_worker_id (`worker_id`),
    UNIQUE KEY uk_user_id (`user_id`),
    KEY idx_user_name (`user_name`),
    KEY idx_worker_name (`worker_name`),
    KEY idx_department_id (`department_id`),
    KEY idx_team_id (`team_id`),
    KEY idx_worker_state (`worker_state`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_general_ci
  ROW_FORMAT = Dynamic
  COMMENT ='工人主表';

DROP TABLE IF EXISTS `production_worker_process`;
CREATE TABLE `production_worker_process`
(
    id                       INT            NOT NULL AUTO_INCREMENT PRIMARY KEY,
    del_state                INT            NOT NULL DEFAULT 0 COMMENT '0-正常 1-删除',
    del_time                 TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '删除时间',
    create_time              TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    worker_process_id        BIGINT         NOT NULL COMMENT '分布式唯一ID',
    worker_id                BIGINT         NOT NULL DEFAULT 0 COMMENT '工人ID',
    user_id                  BIGINT         NOT NULL DEFAULT 0 COMMENT '关联用户ID',
    process_id               BIGINT         NOT NULL DEFAULT 0 COMMENT '工序ID',
    process_code             VARCHAR(64)    NOT NULL DEFAULT '' COMMENT '工序编码',
    process_name             VARCHAR(128)   NOT NULL DEFAULT '' COMMENT '工序名称',
    skill_level              TINYINT        NOT NULL DEFAULT 1 COMMENT '熟练度 1-初级 2-中级 3-高级',
    is_primary               TINYINT        NOT NULL DEFAULT 0 COMMENT '是否主工序 0-否 1-是',
    relation_state           TINYINT        NOT NULL DEFAULT 1 COMMENT '状态 0-停用 1-启用',
    remark                   VARCHAR(1024)  NOT NULL DEFAULT '' COMMENT '备注',
    UNIQUE KEY uk_worker_process_id (`worker_process_id`),
    UNIQUE KEY uk_worker_process (`worker_id`, `process_id`),
    KEY idx_user_id (`user_id`),
    KEY idx_process_id (`process_id`),
    KEY idx_relation_state (`relation_state`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_general_ci
  ROW_FORMAT = Dynamic
  COMMENT ='工人与工序关联表';

DROP TABLE IF EXISTS `production_route`;
CREATE TABLE `production_route`
(
    id                       INT            NOT NULL AUTO_INCREMENT PRIMARY KEY,
    del_state                INT            NOT NULL DEFAULT 0 COMMENT '0-正常 1-删除',
    del_time                 TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '删除时间',
    create_time              TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    route_id                 BIGINT         NOT NULL COMMENT '分布式唯一ID',
    route_code               VARCHAR(64)    NOT NULL COMMENT '工艺路线编码',
    route_name               VARCHAR(128)   NOT NULL DEFAULT '' COMMENT '工艺路线名称',
    version_no               VARCHAR(32)    NOT NULL DEFAULT 'V1.0' COMMENT '版本号',
    product_id               BIGINT         NOT NULL DEFAULT 0 COMMENT '产品ID',
    product_code             VARCHAR(64)    NOT NULL DEFAULT '' COMMENT '产品编码',
    product_name             VARCHAR(128)   NOT NULL DEFAULT '' COMMENT '产品名称',
    product_spec             VARCHAR(128)   NOT NULL DEFAULT '' COMMENT '产品规格',
    workshop_type            TINYINT        NOT NULL DEFAULT 0 COMMENT '车间分类 0-未分类 1-水性车间 2-油性车间 3-其他',
    is_default               TINYINT        NOT NULL DEFAULT 1 COMMENT '是否默认路线 0-否 1-是',
    route_state              TINYINT        NOT NULL DEFAULT 1 COMMENT '状态 0-停用 1-启用',
    maker_user_id            BIGINT         NOT NULL DEFAULT 0 COMMENT '制单人ID',
    maker_user_name          VARCHAR(64)    NOT NULL DEFAULT '' COMMENT '制单人',
    remark                   VARCHAR(1024)  NOT NULL DEFAULT '' COMMENT '备注',
    UNIQUE KEY uk_route_id (`route_id`),
    UNIQUE KEY uk_route_code (`route_code`),
    KEY idx_product_id (`product_id`),
    KEY idx_product_name (`product_name`),
    KEY idx_workshop_type (`workshop_type`),
    KEY idx_route_state (`route_state`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_general_ci
  ROW_FORMAT = Dynamic
  COMMENT ='工艺路线主表';

DROP TABLE IF EXISTS `production_route_process`;
CREATE TABLE `production_route_process`
(
    id                       INT            NOT NULL AUTO_INCREMENT PRIMARY KEY,
    del_state                INT            NOT NULL DEFAULT 0 COMMENT '0-正常 1-删除',
    del_time                 TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '删除时间',
    create_time              TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    route_process_id         BIGINT         NOT NULL COMMENT '分布式唯一ID',
    route_id                 BIGINT         NOT NULL DEFAULT 0 COMMENT '工艺路线ID',
    route_code               VARCHAR(64)    NOT NULL DEFAULT '' COMMENT '工艺路线编码',
    line_no                  INT            NOT NULL DEFAULT 1 COMMENT '工序顺序',
    process_id               BIGINT         NOT NULL DEFAULT 0 COMMENT '工序ID',
    process_code             VARCHAR(64)    NOT NULL DEFAULT '' COMMENT '工序编码',
    process_name             VARCHAR(128)   NOT NULL DEFAULT '' COMMENT '工序名称',
    default_machine_id       BIGINT         NOT NULL DEFAULT 0 COMMENT '默认机台ID',
    default_machine_name     VARCHAR(128)   NOT NULL DEFAULT '' COMMENT '默认机台名称',
    report_required          TINYINT        NOT NULL DEFAULT 1 COMMENT '是否必须报工 0-否 1-是',
    qc_required              TINYINT        NOT NULL DEFAULT 0 COMMENT '是否需要质检 0-否 1-是',
    standard_capacity        DECIMAL(18, 4) NOT NULL DEFAULT 0.0000 COMMENT '标准产能',
    standard_minutes         DECIMAL(18, 2) NOT NULL DEFAULT 0.00 COMMENT '标准工时(分钟)',
    pass_rate                DECIMAL(10, 2) NOT NULL DEFAULT 100.00 COMMENT '目标合格率',
    remark                   VARCHAR(1024)  NOT NULL DEFAULT '' COMMENT '备注',
    UNIQUE KEY uk_route_process_id (`route_process_id`),
    UNIQUE KEY uk_route_line (`route_id`, `line_no`),
    KEY idx_process_id (`process_id`),
    KEY idx_route_code (`route_code`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_general_ci
  ROW_FORMAT = Dynamic
  COMMENT ='工艺路线工序明细表';

DROP TABLE IF EXISTS `production_order`;
CREATE TABLE `production_order`
(
    id                       INT            NOT NULL AUTO_INCREMENT PRIMARY KEY,
    del_state                INT            NOT NULL DEFAULT 0 COMMENT '0-正常 1-删除',
    del_time                 TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '删除时间',
    create_time              TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    production_order_id      BIGINT         NOT NULL COMMENT '分布式唯一ID',
    production_no            VARCHAR(64)    NOT NULL COMMENT '生产单号/生产编号/溯源码',
    source_type              TINYINT        NOT NULL DEFAULT 0 COMMENT '来源类型 0-手工新增 1-销售单生成 2-翻单生成',
    source_production_order_id BIGINT       NOT NULL DEFAULT 0 COMMENT '来源生产单ID',
    source_production_no     VARCHAR(64)    NOT NULL DEFAULT '' COMMENT '来源生产单号',
    sales_order_id           BIGINT         NOT NULL DEFAULT 0 COMMENT '关联销售单ID',
    sales_order_item_id      BIGINT         NOT NULL DEFAULT 0 COMMENT '关联销售单明细ID',
    contract_no              VARCHAR(64)    NOT NULL DEFAULT '' COMMENT '合同编号',
    customer_id              BIGINT         NOT NULL DEFAULT 0 COMMENT '客户ID',
    customer_name            VARCHAR(128)   NOT NULL DEFAULT '' COMMENT '客户名称',
    order_date               DATE           NOT NULL COMMENT '订单日期',
    delivery_date            DATE                    DEFAULT NULL COMMENT '交货日期',
    route_id                 BIGINT         NOT NULL DEFAULT 0 COMMENT '工艺路线ID',
    route_code               VARCHAR(64)    NOT NULL DEFAULT '' COMMENT '工艺路线编码',
    product_id               BIGINT         NOT NULL DEFAULT 0 COMMENT '产品ID',
    product_code             VARCHAR(64)    NOT NULL DEFAULT '' COMMENT '产品编码',
    product_name             VARCHAR(128)   NOT NULL DEFAULT '' COMMENT '产品名称',
    product_spec             VARCHAR(128)   NOT NULL DEFAULT '' COMMENT '产品规格',
    unit_name                VARCHAR(32)    NOT NULL DEFAULT '' COMMENT '单位',
    workshop_type            TINYINT        NOT NULL DEFAULT 0 COMMENT '车间分类 0-未分类 1-水性车间 2-油性车间 3-其他',
    quantity                 DECIMAL(18, 4) NOT NULL DEFAULT 0.0000 COMMENT '生产数量',
    reported_quantity        DECIMAL(18, 4) NOT NULL DEFAULT 0.0000 COMMENT '累计报工数量',
    qualified_quantity       DECIMAL(18, 4) NOT NULL DEFAULT 0.0000 COMMENT '累计合格数量',
    unqualified_quantity     DECIMAL(18, 4) NOT NULL DEFAULT 0.0000 COMMENT '累计不合格数量',
    stocked_quantity         DECIMAL(18, 4) NOT NULL DEFAULT 0.0000 COMMENT '累计入库数量',
    progress_rate            DECIMAL(10, 2) NOT NULL DEFAULT 0.00 COMMENT '生产进度百分比，允许超过100',
    task_count               INT            NOT NULL DEFAULT 0 COMMENT '工序任务数',
    finished_task_count      INT            NOT NULL DEFAULT 0 COMMENT '已完成工序任务数',
    current_process_id       BIGINT         NOT NULL DEFAULT 0 COMMENT '当前工序ID',
    current_process_name     VARCHAR(128)   NOT NULL DEFAULT '' COMMENT '当前工序名称',
    audit_state              TINYINT        NOT NULL DEFAULT 0 COMMENT '审核状态 0-待审核 1-已审核 2-反审核 3-作废',
    production_state         TINYINT        NOT NULL DEFAULT 0 COMMENT '业务状态 0-待生产员审核 1-待排程 2-生产中 3-待报工 4-待入库 5-已完成 6-已取消',
    trace_qrcode_content     VARCHAR(255)   NOT NULL DEFAULT '' COMMENT '追溯二维码内容',
    print_count              INT            NOT NULL DEFAULT 0 COMMENT '打印次数',
    maker_user_id            BIGINT         NOT NULL DEFAULT 0 COMMENT '制单人ID',
    maker_user_name          VARCHAR(64)    NOT NULL DEFAULT '' COMMENT '制单人',
    audit_user_id            BIGINT         NOT NULL DEFAULT 0 COMMENT '审核人ID',
    audit_user_name          VARCHAR(64)    NOT NULL DEFAULT '' COMMENT '审核人',
    audit_time               DATETIME                DEFAULT NULL COMMENT '审核时间',
    complete_time            DATETIME                DEFAULT NULL COMMENT '完工时间',
    remark                   VARCHAR(1024)  NOT NULL DEFAULT '' COMMENT '备注',
    UNIQUE KEY uk_production_order_id (`production_order_id`),
    UNIQUE KEY uk_production_no (`production_no`),
    KEY idx_sales_order_id (`sales_order_id`),
    KEY idx_sales_order_item_id (`sales_order_item_id`),
    KEY idx_contract_no (`contract_no`),
    KEY idx_customer_id (`customer_id`),
    KEY idx_customer_name (`customer_name`),
    KEY idx_product_id (`product_id`),
    KEY idx_product_name (`product_name`),
    KEY idx_order_date (`order_date`),
    KEY idx_delivery_date (`delivery_date`),
    KEY idx_production_state (`production_state`),
    KEY idx_audit_state (`audit_state`),
    KEY idx_workshop_type (`workshop_type`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_general_ci
  ROW_FORMAT = Dynamic
  COMMENT ='生产单主表';

DROP TABLE IF EXISTS `production_order_process`;
CREATE TABLE `production_order_process`
(
    id                       INT            NOT NULL AUTO_INCREMENT PRIMARY KEY,
    del_state                INT            NOT NULL DEFAULT 0 COMMENT '0-正常 1-删除',
    del_time                 TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '删除时间',
    create_time              TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    production_order_process_id BIGINT      NOT NULL COMMENT '分布式唯一ID',
    production_order_id      BIGINT         NOT NULL DEFAULT 0 COMMENT '生产单ID',
    production_no            VARCHAR(64)    NOT NULL DEFAULT '' COMMENT '生产单号',
    sales_order_item_id      BIGINT         NOT NULL DEFAULT 0 COMMENT '销售单明细ID',
    contract_no              VARCHAR(64)    NOT NULL DEFAULT '' COMMENT '合同编号',
    customer_id              BIGINT         NOT NULL DEFAULT 0 COMMENT '客户ID',
    customer_name            VARCHAR(128)   NOT NULL DEFAULT '' COMMENT '客户名称',
    product_id               BIGINT         NOT NULL DEFAULT 0 COMMENT '产品ID',
    product_code             VARCHAR(64)    NOT NULL DEFAULT '' COMMENT '产品编码',
    product_name             VARCHAR(128)   NOT NULL DEFAULT '' COMMENT '产品名称',
    product_spec             VARCHAR(128)   NOT NULL DEFAULT '' COMMENT '产品规格',
    route_id                 BIGINT         NOT NULL DEFAULT 0 COMMENT '工艺路线ID',
    route_code               VARCHAR(64)    NOT NULL DEFAULT '' COMMENT '工艺路线编码',
    line_no                  INT            NOT NULL DEFAULT 1 COMMENT '工序行号',
    process_id               BIGINT         NOT NULL DEFAULT 0 COMMENT '工序ID',
    process_code             VARCHAR(64)    NOT NULL DEFAULT '' COMMENT '工序编码',
    process_name             VARCHAR(128)   NOT NULL DEFAULT '' COMMENT '工序名称',
    team_id                  BIGINT         NOT NULL DEFAULT 0 COMMENT '班组ID',
    team_name                VARCHAR(128)   NOT NULL DEFAULT '' COMMENT '班组名称',
    machine_id               BIGINT         NOT NULL DEFAULT 0 COMMENT '机台ID',
    machine_name             VARCHAR(128)   NOT NULL DEFAULT '' COMMENT '机台名称',
    planned_quantity         DECIMAL(18, 4) NOT NULL DEFAULT 0.0000 COMMENT '计划数量',
    reported_quantity        DECIMAL(18, 4) NOT NULL DEFAULT 0.0000 COMMENT '累计报工数量',
    qualified_quantity       DECIMAL(18, 4) NOT NULL DEFAULT 0.0000 COMMENT '累计合格数量',
    unqualified_quantity     DECIMAL(18, 4) NOT NULL DEFAULT 0.0000 COMMENT '累计不合格数量',
    plan_start_time          DATETIME                DEFAULT NULL COMMENT '计划开始时间',
    plan_end_time            DATETIME                DEFAULT NULL COMMENT '计划结束时间',
    actual_start_time        DATETIME                DEFAULT NULL COMMENT '实际开始时间',
    actual_end_time          DATETIME                DEFAULT NULL COMMENT '实际结束时间',
    estimated_minutes        INT            NOT NULL DEFAULT 0 COMMENT '预计工时(分钟)',
    actual_minutes           INT            NOT NULL DEFAULT 0 COMMENT '实际工时(分钟)',
    progress_rate            DECIMAL(10, 2) NOT NULL DEFAULT 0.00 COMMENT '工序进度百分比，允许超过100',
    report_required          TINYINT        NOT NULL DEFAULT 1 COMMENT '是否必须报工 0-否 1-是',
    is_last_process          TINYINT        NOT NULL DEFAULT 0 COMMENT '是否最后一道工序 0-否 1-是',
    task_state               TINYINT        NOT NULL DEFAULT 0 COMMENT '任务状态 0-待排程 1-待开工 2-生产中 3-待报工 4-已完成 5-暂停 6-取消',
    dispatch_user_id         BIGINT         NOT NULL DEFAULT 0 COMMENT '派工/排程人ID',
    dispatch_user_name       VARCHAR(64)    NOT NULL DEFAULT '' COMMENT '派工/排程人',
    remark                   VARCHAR(1024)  NOT NULL DEFAULT '' COMMENT '备注',
    UNIQUE KEY uk_production_order_process_id (`production_order_process_id`),
    UNIQUE KEY uk_order_process_line (`production_order_id`, `line_no`),
    KEY idx_production_order_id (`production_order_id`),
    KEY idx_production_no (`production_no`),
    KEY idx_contract_no (`contract_no`),
    KEY idx_customer_id (`customer_id`),
    KEY idx_product_id (`product_id`),
    KEY idx_process_id (`process_id`),
    KEY idx_machine_id (`machine_id`),
    KEY idx_team_id (`team_id`),
    KEY idx_task_state (`task_state`),
    KEY idx_plan_start_time (`plan_start_time`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_general_ci
  ROW_FORMAT = Dynamic
  COMMENT ='生产单工序任务表';

DROP TABLE IF EXISTS `production_report`;
CREATE TABLE `production_report`
(
    id                       INT            NOT NULL AUTO_INCREMENT PRIMARY KEY,
    del_state                INT            NOT NULL DEFAULT 0 COMMENT '0-正常 1-删除',
    del_time                 TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '删除时间',
    create_time              TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    report_id                BIGINT         NOT NULL COMMENT '分布式唯一ID',
    report_no                VARCHAR(64)    NOT NULL COMMENT '报工单号',
    production_order_id      BIGINT         NOT NULL DEFAULT 0 COMMENT '生产单ID',
    production_no            VARCHAR(64)    NOT NULL DEFAULT '' COMMENT '生产单号',
    production_order_process_id BIGINT      NOT NULL DEFAULT 0 COMMENT '生产工序任务ID',
    contract_no              VARCHAR(64)    NOT NULL DEFAULT '' COMMENT '合同编号',
    customer_id              BIGINT         NOT NULL DEFAULT 0 COMMENT '客户ID',
    customer_name            VARCHAR(128)   NOT NULL DEFAULT '' COMMENT '客户名称',
    product_id               BIGINT         NOT NULL DEFAULT 0 COMMENT '产品ID',
    product_code             VARCHAR(64)    NOT NULL DEFAULT '' COMMENT '产品编码',
    product_name             VARCHAR(128)   NOT NULL DEFAULT '' COMMENT '产品名称',
    product_spec             VARCHAR(128)   NOT NULL DEFAULT '' COMMENT '产品规格',
    unit_name                VARCHAR(32)    NOT NULL DEFAULT '' COMMENT '单位',
    process_id               BIGINT         NOT NULL DEFAULT 0 COMMENT '工序ID',
    process_code             VARCHAR(64)    NOT NULL DEFAULT '' COMMENT '工序编码',
    process_name             VARCHAR(128)   NOT NULL DEFAULT '' COMMENT '工序名称',
    team_id                  BIGINT         NOT NULL DEFAULT 0 COMMENT '班组ID',
    team_name                VARCHAR(128)   NOT NULL DEFAULT '' COMMENT '班组名称',
    machine_id               BIGINT         NOT NULL DEFAULT 0 COMMENT '机台ID',
    machine_name             VARCHAR(128)   NOT NULL DEFAULT '' COMMENT '机台名称',
    worker_id                BIGINT         NOT NULL DEFAULT 0 COMMENT '工人ID',
    report_user_id           BIGINT         NOT NULL DEFAULT 0 COMMENT '报工员工用户ID',
    report_user_name         VARCHAR(64)    NOT NULL DEFAULT '' COMMENT '报工员工用户名',
    report_date              DATE           NOT NULL COMMENT '报工日期',
    shift_type               TINYINT        NOT NULL DEFAULT 0 COMMENT '班次 0-普通班 1-白班 2-中班 3-晚班',
    start_time               DATETIME                DEFAULT NULL COMMENT '开始时间',
    end_time                 DATETIME                DEFAULT NULL COMMENT '结束时间',
    work_minutes             INT            NOT NULL DEFAULT 0 COMMENT '报工工时(分钟)',
    report_quantity          DECIMAL(18, 4) NOT NULL DEFAULT 0.0000 COMMENT '报工数量',
    qualified_quantity       DECIMAL(18, 4) NOT NULL DEFAULT 0.0000 COMMENT '合格数量',
    unqualified_quantity     DECIMAL(18, 4) NOT NULL DEFAULT 0.0000 COMMENT '不合格数量',
    rework_quantity          DECIMAL(18, 4) NOT NULL DEFAULT 0.0000 COMMENT '返工数量',
    scrap_quantity           DECIMAL(18, 4) NOT NULL DEFAULT 0.0000 COMMENT '报废数量',
    audit_state              TINYINT        NOT NULL DEFAULT 0 COMMENT '审核状态 0-待审核 1-已审核 2-反审核 3-作废',
    audit_user_id            BIGINT         NOT NULL DEFAULT 0 COMMENT '审核人ID',
    audit_user_name          VARCHAR(64)    NOT NULL DEFAULT '' COMMENT '审核人',
    audit_time               DATETIME                DEFAULT NULL COMMENT '审核时间',
    remark                   VARCHAR(1024)  NOT NULL DEFAULT '' COMMENT '备注',
    UNIQUE KEY uk_report_id (`report_id`),
    UNIQUE KEY uk_report_no (`report_no`),
    KEY idx_production_order_id (`production_order_id`),
    KEY idx_production_no (`production_no`),
    KEY idx_process_id (`process_id`),
    KEY idx_machine_id (`machine_id`),
    KEY idx_worker_id (`worker_id`),
    KEY idx_report_user_id (`report_user_id`),
    KEY idx_report_date (`report_date`),
    KEY idx_audit_state (`audit_state`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_general_ci
  ROW_FORMAT = Dynamic
  COMMENT ='工序报工记录表';

DROP TABLE IF EXISTS `production_trace_log`;
CREATE TABLE `production_trace_log`
(
    id                       INT            NOT NULL AUTO_INCREMENT PRIMARY KEY,
    del_state                INT            NOT NULL DEFAULT 0 COMMENT '0-正常 1-删除',
    del_time                 TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '删除时间',
    create_time              TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    trace_log_id             BIGINT         NOT NULL COMMENT '分布式唯一ID',
    production_order_id      BIGINT         NOT NULL DEFAULT 0 COMMENT '生产单ID',
    production_no            VARCHAR(64)    NOT NULL DEFAULT '' COMMENT '生产单号/溯源码',
    trace_code               VARCHAR(64)    NOT NULL DEFAULT '' COMMENT '追溯码',
    contract_no              VARCHAR(64)    NOT NULL DEFAULT '' COMMENT '合同编号',
    product_id               BIGINT         NOT NULL DEFAULT 0 COMMENT '产品ID',
    product_code             VARCHAR(64)    NOT NULL DEFAULT '' COMMENT '产品编码',
    product_name             VARCHAR(128)   NOT NULL DEFAULT '' COMMENT '产品名称',
    product_spec             VARCHAR(128)   NOT NULL DEFAULT '' COMMENT '产品规格',
    process_id               BIGINT         NOT NULL DEFAULT 0 COMMENT '工序ID',
    process_code             VARCHAR(64)    NOT NULL DEFAULT '' COMMENT '工序编码',
    process_name             VARCHAR(128)   NOT NULL DEFAULT '' COMMENT '工序名称',
    machine_id               BIGINT         NOT NULL DEFAULT 0 COMMENT '机台ID',
    machine_name             VARCHAR(128)   NOT NULL DEFAULT '' COMMENT '机台名称',
    event_type               TINYINT        NOT NULL DEFAULT 0 COMMENT '事件类型 0-其他 1-生产单创建 2-审核 3-排程 4-开工 5-报工 6-完工 7-入库 8-反审核 9-翻单 10-打印二维码',
    event_name               VARCHAR(64)    NOT NULL DEFAULT '' COMMENT '事件名称',
    source_table             VARCHAR(64)    NOT NULL DEFAULT '' COMMENT '来源表名',
    source_id                BIGINT         NOT NULL DEFAULT 0 COMMENT '来源业务ID',
    operator_user_id         BIGINT         NOT NULL DEFAULT 0 COMMENT '操作人ID',
    operator_user_name       VARCHAR(64)    NOT NULL DEFAULT '' COMMENT '操作人',
    progress_rate            DECIMAL(10, 2) NOT NULL DEFAULT 0.00 COMMENT '事件发生时进度百分比',
    event_time               DATETIME       NOT NULL COMMENT '事件时间',
    event_content            VARCHAR(1024)  NOT NULL DEFAULT '' COMMENT '事件内容',
    UNIQUE KEY uk_trace_log_id (`trace_log_id`),
    KEY idx_production_order_id (`production_order_id`),
    KEY idx_production_no (`production_no`),
    KEY idx_trace_code (`trace_code`),
    KEY idx_process_id (`process_id`),
    KEY idx_event_type (`event_type`),
    KEY idx_event_time (`event_time`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_general_ci
  ROW_FORMAT = Dynamic
  COMMENT ='生产追溯日志表';

DROP TABLE IF EXISTS `production_schedule`;
CREATE TABLE `production_schedule`
(
    id                       INT            NOT NULL AUTO_INCREMENT PRIMARY KEY,
    del_state                INT            NOT NULL DEFAULT 0 COMMENT '0-正常 1-删除',
    del_time                 TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '删除时间',
    create_time              TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    schedule_id              BIGINT         NOT NULL COMMENT '分布式唯一ID',
    production_order_id      BIGINT         NOT NULL DEFAULT 0 COMMENT '生产单ID',
    production_no            VARCHAR(64)    NOT NULL DEFAULT '' COMMENT '生产单号',
    production_order_process_id BIGINT      NOT NULL DEFAULT 0 COMMENT '生产工序任务ID',
    contract_no              VARCHAR(64)    NOT NULL DEFAULT '' COMMENT '合同编号',
    product_id               BIGINT         NOT NULL DEFAULT 0 COMMENT '产品ID',
    product_code             VARCHAR(64)    NOT NULL DEFAULT '' COMMENT '产品编码',
    product_name             VARCHAR(128)   NOT NULL DEFAULT '' COMMENT '产品名称',
    product_spec             VARCHAR(128)   NOT NULL DEFAULT '' COMMENT '产品规格',
    process_id               BIGINT         NOT NULL DEFAULT 0 COMMENT '工序ID',
    process_code             VARCHAR(64)    NOT NULL DEFAULT '' COMMENT '工序编码',
    process_name             VARCHAR(128)   NOT NULL DEFAULT '' COMMENT '工序名称',
    machine_id               BIGINT         NOT NULL DEFAULT 0 COMMENT '机台ID',
    machine_name             VARCHAR(128)   NOT NULL DEFAULT '' COMMENT '机台名称',
    plan_start_time          DATETIME                DEFAULT NULL COMMENT '计划开始时间',
    plan_end_time            DATETIME                DEFAULT NULL COMMENT '计划结束时间',
    actual_start_time        DATETIME                DEFAULT NULL COMMENT '实际开始时间',
    actual_end_time          DATETIME                DEFAULT NULL COMMENT '实际结束时间',
    consume_days             DECIMAL(10, 2) NOT NULL DEFAULT 0.00 COMMENT '消耗天数',
    schedule_state           TINYINT        NOT NULL DEFAULT 0 COMMENT '排程状态 0-待排程 1-已排程 2-生产中 3-已完成 4-已取消',
    sort_no                  INT            NOT NULL DEFAULT 0 COMMENT '排序值',
    scheduler_user_id        BIGINT         NOT NULL DEFAULT 0 COMMENT '排程人ID',
    scheduler_user_name      VARCHAR(64)    NOT NULL DEFAULT '' COMMENT '排程人',
    remark                   VARCHAR(1024)  NOT NULL DEFAULT '' COMMENT '备注',
    UNIQUE KEY uk_schedule_id (`schedule_id`),
    KEY idx_production_order_id (`production_order_id`),
    KEY idx_production_no (`production_no`),
    KEY idx_production_order_process_id (`production_order_process_id`),
    KEY idx_machine_id (`machine_id`),
    KEY idx_plan_start_time (`plan_start_time`),
    KEY idx_plan_end_time (`plan_end_time`),
    KEY idx_schedule_state (`schedule_state`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_general_ci
  ROW_FORMAT = Dynamic
  COMMENT ='生产排程表';
