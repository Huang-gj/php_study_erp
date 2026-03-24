
SET NAMES utf8mb4;

DROP TABLE IF EXISTS `sales_customer`;
CREATE TABLE `sales_customer`
(
    id                     INT            NOT NULL AUTO_INCREMENT PRIMARY KEY,
    del_state              INT            NOT NULL DEFAULT 0 COMMENT '0-正常 1-删除',
    del_time               TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '删除时间',
    create_time            TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    customer_id            BIGINT         NOT NULL COMMENT '分布式唯一ID',
    customer_code          VARCHAR(64)    NOT NULL COMMENT '客户编号',
    customer_name          VARCHAR(128)   NOT NULL DEFAULT '' COMMENT '客户名称',
    customer_short_name    VARCHAR(128)   NOT NULL DEFAULT '' COMMENT '客户简称',
    contact_name           VARCHAR(64)    NOT NULL DEFAULT '' COMMENT '联系人',
    phone_number           VARCHAR(20)    NOT NULL DEFAULT '' COMMENT '联系电话',
    telephone              VARCHAR(20)    NOT NULL DEFAULT '' COMMENT '座机号码',
    email                  VARCHAR(128)   NOT NULL DEFAULT '' COMMENT '邮箱',
    tax_no                 VARCHAR(64)    NOT NULL DEFAULT '' COMMENT '税号',
    bank_name              VARCHAR(128)   NOT NULL DEFAULT '' COMMENT '开户银行',
    bank_account           VARCHAR(128)   NOT NULL DEFAULT '' COMMENT '银行账号',
    bank_address           VARCHAR(255)   NOT NULL DEFAULT '' COMMENT '开户地址',
    province               VARCHAR(64)    NOT NULL DEFAULT '' COMMENT '省',
    city                   VARCHAR(64)    NOT NULL DEFAULT '' COMMENT '市',
    district               VARCHAR(64)    NOT NULL DEFAULT '' COMMENT '区',
    detail_address         VARCHAR(255)   NOT NULL DEFAULT '' COMMENT '详细地址',
    delivery_address       VARCHAR(255)   NOT NULL DEFAULT '' COMMENT '默认收货地址',
    invoice_address        VARCHAR(255)   NOT NULL DEFAULT '' COMMENT '默认开票地址',
    settlement_type        TINYINT        NOT NULL DEFAULT 0 COMMENT '结算方式 0-其他 1-现结 2-月结 3-预付',
    default_payment_method TINYINT        NOT NULL DEFAULT 0 COMMENT '默认收款方式 0-其他 1-现金 2-转账 3-微信 4-支付宝 5-承兑',
    default_tax_rate       DECIMAL(10, 2) NOT NULL DEFAULT 0.00 COMMENT '默认税率',
    credit_limit           DECIMAL(18, 2) NOT NULL DEFAULT 0.00 COMMENT '信用额度',
    owner_user_id          BIGINT         NOT NULL DEFAULT 0 COMMENT '归属业务员ID',
    owner_user_name        VARCHAR(64)    NOT NULL DEFAULT '' COMMENT '归属业务员',
    customer_state         TINYINT        NOT NULL DEFAULT 1 COMMENT '0-停用 1-启用',
    remark                 VARCHAR(1024)  NOT NULL DEFAULT '' COMMENT '备注',
    UNIQUE KEY uk_customer_id (`customer_id`),
    UNIQUE KEY uk_customer_code (`customer_code`),
    KEY idx_customer_name (`customer_name`),
    KEY idx_owner_user_id (`owner_user_id`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_general_ci
  ROW_FORMAT = Dynamic
  COMMENT ='客户主表';

DROP TABLE IF EXISTS `sales_product`;
CREATE TABLE `sales_product`
(
    id                     INT            NOT NULL AUTO_INCREMENT PRIMARY KEY,
    del_state              INT            NOT NULL DEFAULT 0 COMMENT '0-正常 1-删除',
    del_time               TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '删除时间',
    create_time            TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    product_id             BIGINT         NOT NULL COMMENT '分布式唯一ID',
    product_code           VARCHAR(64)    NOT NULL COMMENT '产品编码/存货编码',
    product_name           VARCHAR(128)   NOT NULL DEFAULT '' COMMENT '产品名称',
    product_alias          VARCHAR(128)   NOT NULL DEFAULT '' COMMENT '产品别名',
    product_spec           VARCHAR(128)   NOT NULL DEFAULT '' COMMENT '产品规格',
    unit_name              VARCHAR(32)    NOT NULL DEFAULT '' COMMENT '单位',
    product_category       VARCHAR(64)    NOT NULL DEFAULT '' COMMENT '产品分类',
    workshop_type          TINYINT        NOT NULL DEFAULT 0 COMMENT '车间分类 0-未分类 1-水性车间 2-油性车间 3-其他',
    tax_rate               DECIMAL(10, 2) NOT NULL DEFAULT 0.00 COMMENT '默认税率',
    default_price          DECIMAL(18, 4) NOT NULL DEFAULT 0.0000 COMMENT '默认未税单价',
    default_tax_price      DECIMAL(18, 4) NOT NULL DEFAULT 0.0000 COMMENT '默认含税单价',
    safe_stock_quantity    DECIMAL(18, 4) NOT NULL DEFAULT 0.0000 COMMENT '安全库存',
    current_stock_quantity DECIMAL(18, 4) NOT NULL DEFAULT 0.0000 COMMENT '当前库存',
    product_state          TINYINT        NOT NULL DEFAULT 1 COMMENT '0-停用 1-启用',
    remark                 VARCHAR(1024)  NOT NULL DEFAULT '' COMMENT '备注',
    UNIQUE KEY uk_product_id (`product_id`),
    UNIQUE KEY uk_product_code (`product_code`),
    KEY idx_product_name (`product_name`),
    KEY idx_workshop_type (`workshop_type`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_general_ci
  ROW_FORMAT = Dynamic
  COMMENT ='产品主表';

DROP TABLE IF EXISTS `sales_business_order`;
CREATE TABLE `sales_business_order`
(
    id                    INT            NOT NULL AUTO_INCREMENT PRIMARY KEY,
    del_state             INT            NOT NULL DEFAULT 0 COMMENT '0-正常 1-删除',
    del_time              TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '删除时间',
    create_time           TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    business_order_id     BIGINT         NOT NULL COMMENT '分布式唯一ID',
    business_order_no     VARCHAR(64)    NOT NULL COMMENT '业务单编号',
    customer_id           BIGINT         NOT NULL DEFAULT 0 COMMENT '客户ID',
    customer_name         VARCHAR(128)   NOT NULL DEFAULT '' COMMENT '客户名称',
    order_date            DATE           NOT NULL COMMENT '订单日期',
    delivery_date         DATE                    DEFAULT NULL COMMENT '交货日期',
    tax_rate              DECIMAL(10, 2) NOT NULL DEFAULT 0.00 COMMENT '整单税率',
    item_count            INT            NOT NULL DEFAULT 0 COMMENT '明细行数',
    total_quantity        DECIMAL(18, 4) NOT NULL DEFAULT 0.0000 COMMENT '总数量',
    total_amount          DECIMAL(18, 2) NOT NULL DEFAULT 0.00 COMMENT '未税总金额',
    total_tax_amount      DECIMAL(18, 2) NOT NULL DEFAULT 0.00 COMMENT '含税总金额',
    audit_state           TINYINT        NOT NULL DEFAULT 0 COMMENT '审核状态 0-待审核 1-已审核 2-反审核 3-作废',
    convert_state         TINYINT        NOT NULL DEFAULT 0 COMMENT '转销售单状态 0-未转 1-部分转 2-已转',
    maker_user_id         BIGINT         NOT NULL DEFAULT 0 COMMENT '制单人ID',
    maker_user_name       VARCHAR(64)    NOT NULL DEFAULT '' COMMENT '制单人',
    audit_user_id         BIGINT         NOT NULL DEFAULT 0 COMMENT '审核人ID',
    audit_user_name       VARCHAR(64)    NOT NULL DEFAULT '' COMMENT '审核人',
    audit_time            DATETIME                DEFAULT NULL COMMENT '审核时间',
    source_type           TINYINT        NOT NULL DEFAULT 0 COMMENT '来源类型 0-手工新增 1-翻单生成',
    source_sales_order_id BIGINT         NOT NULL DEFAULT 0 COMMENT '来源销售单ID',
    source_contract_no    VARCHAR(64)    NOT NULL DEFAULT '' COMMENT '来源合同编号',
    remark                VARCHAR(1024)  NOT NULL DEFAULT '' COMMENT '备注',
    UNIQUE KEY uk_business_order_id (`business_order_id`),
    UNIQUE KEY uk_business_order_no (`business_order_no`),
    KEY idx_customer_id (`customer_id`),
    KEY idx_customer_name (`customer_name`),
    KEY idx_order_date (`order_date`),
    KEY idx_audit_state (`audit_state`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_general_ci
  ROW_FORMAT = Dynamic
  COMMENT ='业务单主表';

DROP TABLE IF EXISTS `sales_business_order_item`;
CREATE TABLE `sales_business_order_item`
(
    id                     INT            NOT NULL AUTO_INCREMENT PRIMARY KEY,
    del_state              INT            NOT NULL DEFAULT 0 COMMENT '0-正常 1-删除',
    del_time               TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '删除时间',
    create_time            TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    business_order_item_id BIGINT         NOT NULL COMMENT '分布式唯一ID',
    business_order_id      BIGINT         NOT NULL DEFAULT 0 COMMENT '业务单ID',
    business_order_no      VARCHAR(64)    NOT NULL DEFAULT '' COMMENT '业务单编号',
    line_no                INT            NOT NULL DEFAULT 1 COMMENT '行号',
    product_id             BIGINT         NOT NULL DEFAULT 0 COMMENT '产品ID',
    product_code           VARCHAR(64)    NOT NULL DEFAULT '' COMMENT '产品编码',
    product_name           VARCHAR(128)   NOT NULL DEFAULT '' COMMENT '产品名称',
    product_spec           VARCHAR(128)   NOT NULL DEFAULT '' COMMENT '产品规格',
    unit_name              VARCHAR(32)    NOT NULL DEFAULT '' COMMENT '单位',
    quantity               DECIMAL(18, 4) NOT NULL DEFAULT 0.0000 COMMENT '数量',
    tax_rate               DECIMAL(10, 2) NOT NULL DEFAULT 0.00 COMMENT '税率',
    price                  DECIMAL(18, 4) NOT NULL DEFAULT 0.0000 COMMENT '未税单价',
    tax_price              DECIMAL(18, 4) NOT NULL DEFAULT 0.0000 COMMENT '含税单价',
    amount                 DECIMAL(18, 2) NOT NULL DEFAULT 0.00 COMMENT '未税金额',
    tax_amount             DECIMAL(18, 2) NOT NULL DEFAULT 0.00 COMMENT '含税金额',
    remark                 VARCHAR(1024)  NOT NULL DEFAULT '' COMMENT '备注',
    UNIQUE KEY uk_business_order_item_id (`business_order_item_id`),
    UNIQUE KEY uk_business_order_line (`business_order_id`, `line_no`),
    KEY idx_product_id (`product_id`),
    KEY idx_product_name (`product_name`),
    KEY idx_business_order_no (`business_order_no`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_general_ci
  ROW_FORMAT = Dynamic
  COMMENT ='业务单明细表';

DROP TABLE IF EXISTS `sales_order`;
CREATE TABLE `sales_order`
(
    id                    INT            NOT NULL AUTO_INCREMENT PRIMARY KEY,
    del_state             INT            NOT NULL DEFAULT 0 COMMENT '0-正常 1-删除',
    del_time              TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '删除时间',
    create_time           TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    sales_order_id        BIGINT         NOT NULL COMMENT '分布式唯一ID',
    contract_no           VARCHAR(64)    NOT NULL COMMENT '合同编号/销售单号',
    business_order_id     BIGINT         NOT NULL DEFAULT 0 COMMENT '来源业务单ID',
    business_order_no     VARCHAR(64)    NOT NULL DEFAULT '' COMMENT '来源业务单号',
    source_type           TINYINT        NOT NULL DEFAULT 0 COMMENT '来源类型 0-手工新增 1-业务单生成 2-翻单生成',
    source_sales_order_id BIGINT         NOT NULL DEFAULT 0 COMMENT '来源销售单ID',
    source_contract_no    VARCHAR(64)    NOT NULL DEFAULT '' COMMENT '来源合同编号',
    customer_id           BIGINT         NOT NULL DEFAULT 0 COMMENT '客户ID',
    customer_name         VARCHAR(128)   NOT NULL DEFAULT '' COMMENT '客户名称',
    customer_tax_no       VARCHAR(64)    NOT NULL DEFAULT '' COMMENT '客户税号',
    order_type            TINYINT        NOT NULL DEFAULT 1 COMMENT '订单类型 1-销售单 2-翻单',
    order_date            DATE           NOT NULL COMMENT '订单日期',
    delivery_date         DATE                    DEFAULT NULL COMMENT '交货日期',
    audit_state           TINYINT        NOT NULL DEFAULT 0 COMMENT '审核状态 0-待审核 1-已审核 2-反审核 3-作废',
    order_state           TINYINT        NOT NULL DEFAULT 0 COMMENT '订单状态 0-待生产员审核 1-待销售主管审核 2-生产中 3-待报工 4-待入库 5-等待出库 6-待发货 7-已完成 8-已取消',
    ship_state            TINYINT        NOT NULL DEFAULT 0 COMMENT '发货状态 0-未发货 1-部分发货 2-全部发货',
    invoice_state         TINYINT        NOT NULL DEFAULT 0 COMMENT '开票状态 0-未开票 1-部分开票 2-全部开票',
    payment_state         TINYINT        NOT NULL DEFAULT 0 COMMENT '收款状态 0-未收款 1-部分收款 2-已收清',
    reconcile_state       TINYINT        NOT NULL DEFAULT 0 COMMENT '对账状态 0-未对账 1-已对账',
    after_sale_state      TINYINT        NOT NULL DEFAULT 0 COMMENT '售后状态 0-无 1-处理中 2-已关闭',
    current_step          TINYINT        NOT NULL DEFAULT 1 COMMENT '当前进度节点 1-销售 2-生产 3-报工 4-入库 5-发货',
    tax_rate              DECIMAL(10, 2) NOT NULL DEFAULT 0.00 COMMENT '整单税率',
    item_count            INT            NOT NULL DEFAULT 0 COMMENT '明细行数',
    total_quantity        DECIMAL(18, 4) NOT NULL DEFAULT 0.0000 COMMENT '总数量',
    total_amount          DECIMAL(18, 2) NOT NULL DEFAULT 0.00 COMMENT '未税总金额',
    total_tax_amount      DECIMAL(18, 2) NOT NULL DEFAULT 0.00 COMMENT '含税总金额/销售总价',
    discount_rate         DECIMAL(10, 2) NOT NULL DEFAULT 0.00 COMMENT '折扣率',
    discount_amount       DECIMAL(18, 2) NOT NULL DEFAULT 0.00 COMMENT '优惠金额',
    logistics_fee         DECIMAL(18, 2) NOT NULL DEFAULT 0.00 COMMENT '物流费用',
    other_fee             DECIMAL(18, 2) NOT NULL DEFAULT 0.00 COMMENT '其他费用',
    shipped_quantity      DECIMAL(18, 4) NOT NULL DEFAULT 0.0000 COMMENT '累计发货数量',
    production_quantity   DECIMAL(18, 4) NOT NULL DEFAULT 0.0000 COMMENT '累计生产数量',
    reported_quantity     DECIMAL(18, 4) NOT NULL DEFAULT 0.0000 COMMENT '累计报工数量',
    stocked_quantity      DECIMAL(18, 4) NOT NULL DEFAULT 0.0000 COMMENT '累计入库数量',
    received_amount       DECIMAL(18, 2) NOT NULL DEFAULT 0.00 COMMENT '累计收款金额',
    receivable_amount     DECIMAL(18, 2) NOT NULL DEFAULT 0.00 COMMENT '应收金额',
    unpaid_amount         DECIMAL(18, 2) NOT NULL DEFAULT 0.00 COMMENT '未收金额',
    opened_invoice_amount DECIMAL(18, 2) NOT NULL DEFAULT 0.00 COMMENT '已开票金额',
    payment_method        TINYINT        NOT NULL DEFAULT 0 COMMENT '收款方式 0-其他 1-现金 2-转账 3-微信 4-支付宝 5-承兑',
    invoice_required      TINYINT        NOT NULL DEFAULT 0 COMMENT '是否开票 0-否 1-是',
    load_date             DATE                    DEFAULT NULL COMMENT '装车日期',
    drawer_user_id        BIGINT         NOT NULL DEFAULT 0 COMMENT '开单员ID',
    drawer_user_name      VARCHAR(64)    NOT NULL DEFAULT '' COMMENT '开单员',
    salesperson_user_id   BIGINT         NOT NULL DEFAULT 0 COMMENT '业务员ID',
    salesperson_user_name VARCHAR(64)    NOT NULL DEFAULT '' COMMENT '业务员',
    audit_user_id         BIGINT         NOT NULL DEFAULT 0 COMMENT '审核人ID',
    audit_user_name       VARCHAR(64)    NOT NULL DEFAULT '' COMMENT '审核人',
    audit_time            DATETIME                DEFAULT NULL COMMENT '审核时间',
    print_count           INT            NOT NULL DEFAULT 0 COMMENT '打印次数',
    remark                VARCHAR(1024)  NOT NULL DEFAULT '' COMMENT '备注',
    UNIQUE KEY uk_sales_order_id (`sales_order_id`),
    UNIQUE KEY uk_contract_no (`contract_no`),
    KEY idx_customer_id (`customer_id`),
    KEY idx_customer_name (`customer_name`),
    KEY idx_order_date (`order_date`),
    KEY idx_delivery_date (`delivery_date`),
    KEY idx_audit_state (`audit_state`),
    KEY idx_order_state (`order_state`),
    KEY idx_ship_state (`ship_state`),
    KEY idx_invoice_state (`invoice_state`),
    KEY idx_salesperson_user_id (`salesperson_user_id`),
    KEY idx_drawer_user_id (`drawer_user_id`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_general_ci
  ROW_FORMAT = Dynamic
  COMMENT ='销售单主表';

DROP TABLE IF EXISTS `sales_order_item`;
CREATE TABLE `sales_order_item`
(
    id                      INT            NOT NULL AUTO_INCREMENT PRIMARY KEY,
    del_state               INT            NOT NULL DEFAULT 0 COMMENT '0-正常 1-删除',
    del_time                TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '删除时间',
    create_time             TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    sales_order_item_id     BIGINT         NOT NULL COMMENT '分布式唯一ID',
    sales_order_id          BIGINT         NOT NULL DEFAULT 0 COMMENT '销售单ID',
    contract_no             VARCHAR(64)    NOT NULL DEFAULT '' COMMENT '合同编号',
    line_no                 INT            NOT NULL DEFAULT 1 COMMENT '行号',
    customer_id             BIGINT         NOT NULL DEFAULT 0 COMMENT '客户ID',
    customer_name           VARCHAR(128)   NOT NULL DEFAULT '' COMMENT '客户名称',
    order_date              DATE           NOT NULL COMMENT '订单日期',
    product_id              BIGINT         NOT NULL DEFAULT 0 COMMENT '产品ID',
    product_code            VARCHAR(64)    NOT NULL DEFAULT '' COMMENT '产品编码',
    product_name            VARCHAR(128)   NOT NULL DEFAULT '' COMMENT '产品名称',
    product_spec            VARCHAR(128)   NOT NULL DEFAULT '' COMMENT '产品规格',
    unit_name               VARCHAR(32)    NOT NULL DEFAULT '' COMMENT '单位',
    workshop_type           TINYINT        NOT NULL DEFAULT 0 COMMENT '车间分类 0-未分类 1-水性车间 2-油性车间 3-其他',
    quantity                DECIMAL(18, 4) NOT NULL DEFAULT 0.0000 COMMENT '订单数量',
    production_quantity     DECIMAL(18, 4) NOT NULL DEFAULT 0.0000 COMMENT '生产数量',
    reported_quantity       DECIMAL(18, 4) NOT NULL DEFAULT 0.0000 COMMENT '报工数量',
    stocked_quantity        DECIMAL(18, 4) NOT NULL DEFAULT 0.0000 COMMENT '入库数量',
    shipped_quantity        DECIMAL(18, 4) NOT NULL DEFAULT 0.0000 COMMENT '发货数量',
    return_quantity         DECIMAL(18, 4) NOT NULL DEFAULT 0.0000 COMMENT '退货数量',
    invoice_quantity        DECIMAL(18, 4) NOT NULL DEFAULT 0.0000 COMMENT '开票数量',
    expected_stock_quantity DECIMAL(18, 4) NOT NULL DEFAULT 0.0000 COMMENT '预计库存/库存快照',
    tax_rate                DECIMAL(10, 2) NOT NULL DEFAULT 0.00 COMMENT '税率',
    price                   DECIMAL(18, 4) NOT NULL DEFAULT 0.0000 COMMENT '未税单价',
    tax_price               DECIMAL(18, 4) NOT NULL DEFAULT 0.0000 COMMENT '含税单价',
    amount                  DECIMAL(18, 2) NOT NULL DEFAULT 0.00 COMMENT '未税金额',
    tax_amount              DECIMAL(18, 2) NOT NULL DEFAULT 0.00 COMMENT '含税金额',
    is_gift                 TINYINT        NOT NULL DEFAULT 0 COMMENT '是否赠品 0-否 1-是',
    remark                  VARCHAR(1024)  NOT NULL DEFAULT '' COMMENT '备注',
    UNIQUE KEY uk_sales_order_item_id (`sales_order_item_id`),
    UNIQUE KEY uk_sales_order_line (`sales_order_id`, `line_no`),
    KEY idx_sales_order_id (`sales_order_id`),
    KEY idx_contract_no (`contract_no`),
    KEY idx_customer_id (`customer_id`),
    KEY idx_product_id (`product_id`),
    KEY idx_product_name (`product_name`),
    KEY idx_workshop_type (`workshop_type`),
    KEY idx_order_date (`order_date`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_general_ci
  ROW_FORMAT = Dynamic
  COMMENT ='销售单明细表';

DROP TABLE IF EXISTS `sales_order_progress_log`;
CREATE TABLE `sales_order_progress_log`
(
    id                 INT           NOT NULL AUTO_INCREMENT PRIMARY KEY,
    del_state          INT           NOT NULL DEFAULT 0 COMMENT '0-正常 1-删除',
    del_time           TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '删除时间',
    create_time        TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    progress_log_id    BIGINT        NOT NULL COMMENT '分布式唯一ID',
    sales_order_id     BIGINT        NOT NULL DEFAULT 0 COMMENT '销售单ID',
    contract_no        VARCHAR(64)   NOT NULL DEFAULT '' COMMENT '合同编号',
    step_code          TINYINT       NOT NULL DEFAULT 1 COMMENT '节点编码 1-销售 2-生产 3-报工 4-入库 5-发货',
    step_name          VARCHAR(32)   NOT NULL DEFAULT '' COMMENT '节点名称',
    step_state         TINYINT       NOT NULL DEFAULT 0 COMMENT '节点状态 0-未开始 1-进行中 2-已完成',
    start_time         DATETIME               DEFAULT NULL COMMENT '开始时间',
    finish_time        DATETIME               DEFAULT NULL COMMENT '完成时间',
    operator_user_id   BIGINT        NOT NULL DEFAULT 0 COMMENT '操作人ID',
    operator_user_name VARCHAR(64)   NOT NULL DEFAULT '' COMMENT '操作人',
    related_no         VARCHAR(64)   NOT NULL DEFAULT '' COMMENT '关联单号',
    remark             VARCHAR(1024) NOT NULL DEFAULT '' COMMENT '备注',
    UNIQUE KEY uk_progress_log_id (`progress_log_id`),
    KEY idx_sales_order_id (`sales_order_id`),
    KEY idx_contract_no (`contract_no`),
    KEY idx_step_code (`step_code`),
    KEY idx_step_state (`step_state`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_general_ci
  ROW_FORMAT = Dynamic
  COMMENT ='销售单进度日志表';

DROP TABLE IF EXISTS `sales_outbound`;
CREATE TABLE `sales_outbound`
(
    id                     INT            NOT NULL AUTO_INCREMENT PRIMARY KEY,
    del_state              INT            NOT NULL DEFAULT 0 COMMENT '0-正常 1-删除',
    del_time               TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '删除时间',
    create_time            TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    outbound_id            BIGINT         NOT NULL COMMENT '分布式唯一ID',
    outbound_no            VARCHAR(64)    NOT NULL COMMENT '出库单号/发货单号',
    sales_order_id         BIGINT         NOT NULL DEFAULT 0 COMMENT '销售单ID',
    contract_no            VARCHAR(64)    NOT NULL DEFAULT '' COMMENT '合同编号',
    customer_id            BIGINT         NOT NULL DEFAULT 0 COMMENT '客户ID',
    customer_name          VARCHAR(128)   NOT NULL DEFAULT '' COMMENT '客户名称',
    document_date          DATE           NOT NULL COMMENT '单据日期',
    ship_date              DATE                    DEFAULT NULL COMMENT '发货日期',
    order_date             DATE                    DEFAULT NULL COMMENT '订单日期',
    audit_state            TINYINT        NOT NULL DEFAULT 0 COMMENT '审核状态 0-待审核 1-已审核 2-反审核 3-作废',
    invoice_required       TINYINT        NOT NULL DEFAULT 0 COMMENT '是否开票 0-否 1-是',
    invoice_state          TINYINT        NOT NULL DEFAULT 0 COMMENT '开票状态 0-未开票 1-部分开票 2-全部开票',
    total_quantity         DECIMAL(18, 4) NOT NULL DEFAULT 0.0000 COMMENT '总出库数量',
    total_amount           DECIMAL(18, 2) NOT NULL DEFAULT 0.00 COMMENT '总金额',
    logistics_fee          DECIMAL(18, 2) NOT NULL DEFAULT 0.00 COMMENT '运输费用',
    express_no             VARCHAR(64)    NOT NULL DEFAULT '' COMMENT '快递单号',
    driver_name            VARCHAR(64)    NOT NULL DEFAULT '' COMMENT '司机姓名',
    vehicle_no             VARCHAR(32)    NOT NULL DEFAULT '' COMMENT '车牌号',
    receiver_name          VARCHAR(64)    NOT NULL DEFAULT '' COMMENT '收货人',
    receiver_phone         VARCHAR(20)    NOT NULL DEFAULT '' COMMENT '收货电话',
    receiver_address       VARCHAR(255)   NOT NULL DEFAULT '' COMMENT '收货地址',
    maker_user_id          BIGINT         NOT NULL DEFAULT 0 COMMENT '制单人ID',
    maker_user_name        VARCHAR(64)    NOT NULL DEFAULT '' COMMENT '制单人',
    audit_user_id          BIGINT         NOT NULL DEFAULT 0 COMMENT '审核人ID',
    audit_user_name        VARCHAR(64)    NOT NULL DEFAULT '' COMMENT '审核人',
    audit_time             DATETIME                DEFAULT NULL COMMENT '审核时间',
    print_count            INT            NOT NULL DEFAULT 0 COMMENT '打印次数',
    print_without_price_count INT         NOT NULL DEFAULT 0 COMMENT '不含单价打印次数',
    remark                 VARCHAR(1024)  NOT NULL DEFAULT '' COMMENT '备注',
    UNIQUE KEY uk_outbound_id (`outbound_id`),
    UNIQUE KEY uk_outbound_no (`outbound_no`),
    KEY idx_sales_order_id (`sales_order_id`),
    KEY idx_contract_no (`contract_no`),
    KEY idx_customer_id (`customer_id`),
    KEY idx_customer_name (`customer_name`),
    KEY idx_document_date (`document_date`),
    KEY idx_ship_date (`ship_date`),
    KEY idx_audit_state (`audit_state`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_general_ci
  ROW_FORMAT = Dynamic
  COMMENT ='产品出库/发货主表';

DROP TABLE IF EXISTS `sales_outbound_item`;
CREATE TABLE `sales_outbound_item`
(
    id                INT            NOT NULL AUTO_INCREMENT PRIMARY KEY,
    del_state         INT            NOT NULL DEFAULT 0 COMMENT '0-正常 1-删除',
    del_time          TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '删除时间',
    create_time       TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    outbound_item_id  BIGINT         NOT NULL COMMENT '分布式唯一ID',
    outbound_id       BIGINT         NOT NULL DEFAULT 0 COMMENT '出库单ID',
    outbound_no       VARCHAR(64)    NOT NULL DEFAULT '' COMMENT '出库单号',
    sales_order_id    BIGINT         NOT NULL DEFAULT 0 COMMENT '销售单ID',
    sales_order_item_id BIGINT       NOT NULL DEFAULT 0 COMMENT '销售单明细ID',
    contract_no       VARCHAR(64)    NOT NULL DEFAULT '' COMMENT '合同编号',
    line_no           INT            NOT NULL DEFAULT 1 COMMENT '行号',
    product_id        BIGINT         NOT NULL DEFAULT 0 COMMENT '产品ID',
    product_code      VARCHAR(64)    NOT NULL DEFAULT '' COMMENT '产品编码',
    product_name      VARCHAR(128)   NOT NULL DEFAULT '' COMMENT '产品名称',
    product_spec      VARCHAR(128)   NOT NULL DEFAULT '' COMMENT '产品规格',
    warehouse_name    VARCHAR(128)   NOT NULL DEFAULT '' COMMENT '仓库名称',
    unit_name         VARCHAR(32)    NOT NULL DEFAULT '' COMMENT '单位',
    outbound_quantity DECIMAL(18, 4) NOT NULL DEFAULT 0.0000 COMMENT '出库数量',
    price             DECIMAL(18, 4) NOT NULL DEFAULT 0.0000 COMMENT '未税单价',
    tax_price         DECIMAL(18, 4) NOT NULL DEFAULT 0.0000 COMMENT '含税单价',
    amount            DECIMAL(18, 2) NOT NULL DEFAULT 0.00 COMMENT '未税金额',
    tax_amount        DECIMAL(18, 2) NOT NULL DEFAULT 0.00 COMMENT '含税金额',
    remark            VARCHAR(1024)  NOT NULL DEFAULT '' COMMENT '备注',
    UNIQUE KEY uk_outbound_item_id (`outbound_item_id`),
    UNIQUE KEY uk_outbound_line (`outbound_id`, `line_no`),
    KEY idx_outbound_no (`outbound_no`),
    KEY idx_sales_order_id (`sales_order_id`),
    KEY idx_sales_order_item_id (`sales_order_item_id`),
    KEY idx_product_id (`product_id`),
    KEY idx_product_name (`product_name`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_general_ci
  ROW_FORMAT = Dynamic
  COMMENT ='产品出库/发货明细表';

DROP TABLE IF EXISTS `sales_invoice`;
CREATE TABLE `sales_invoice`
(
    id               INT            NOT NULL AUTO_INCREMENT PRIMARY KEY,
    del_state        INT            NOT NULL DEFAULT 0 COMMENT '0-正常 1-删除',
    del_time         TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '删除时间',
    create_time      TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    invoice_id       BIGINT         NOT NULL COMMENT '分布式唯一ID',
    invoice_no       VARCHAR(64)    NOT NULL COMMENT '发票号',
    customer_id      BIGINT         NOT NULL DEFAULT 0 COMMENT '客户ID',
    customer_name    VARCHAR(128)   NOT NULL DEFAULT '' COMMENT '客户名称',
    buyer_tax_no     VARCHAR(64)    NOT NULL DEFAULT '' COMMENT '购买方税号',
    invoice_type     TINYINT        NOT NULL DEFAULT 1 COMMENT '发票类型 1-专票 2-普票',
    invoice_date     DATE           NOT NULL COMMENT '开票日期',
    untaxed_amount   DECIMAL(18, 2) NOT NULL DEFAULT 0.00 COMMENT '未税金额',
    tax_amount       DECIMAL(18, 2) NOT NULL DEFAULT 0.00 COMMENT '税额',
    invoice_amount   DECIMAL(18, 2) NOT NULL DEFAULT 0.00 COMMENT '开票金额',
    drawer_user_id   BIGINT         NOT NULL DEFAULT 0 COMMENT '开票人ID',
    drawer_user_name VARCHAR(64)    NOT NULL DEFAULT '' COMMENT '开票人',
    audit_state      TINYINT        NOT NULL DEFAULT 0 COMMENT '审核状态 0-待审核 1-已审核 2-反审核',
    audit_user_id    BIGINT         NOT NULL DEFAULT 0 COMMENT '审核人ID',
    audit_user_name  VARCHAR(64)    NOT NULL DEFAULT '' COMMENT '审核人',
    audit_time       DATETIME                DEFAULT NULL COMMENT '审核时间',
    remark           VARCHAR(1024)  NOT NULL DEFAULT '' COMMENT '备注',
    UNIQUE KEY uk_invoice_id (`invoice_id`),
    UNIQUE KEY uk_invoice_no (`invoice_no`),
    KEY idx_customer_id (`customer_id`),
    KEY idx_customer_name (`customer_name`),
    KEY idx_invoice_date (`invoice_date`),
    KEY idx_audit_state (`audit_state`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_general_ci
  ROW_FORMAT = Dynamic
  COMMENT ='发货开票主表';

DROP TABLE IF EXISTS `sales_invoice_bind`;
CREATE TABLE `sales_invoice_bind`
(
    id               INT            NOT NULL AUTO_INCREMENT PRIMARY KEY,
    del_state        INT            NOT NULL DEFAULT 0 COMMENT '0-正常 1-删除',
    del_time         TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '删除时间',
    create_time      TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    invoice_bind_id  BIGINT         NOT NULL COMMENT '分布式唯一ID',
    invoice_id       BIGINT         NOT NULL DEFAULT 0 COMMENT '发票ID',
    invoice_no       VARCHAR(64)    NOT NULL DEFAULT '' COMMENT '发票号',
    sales_order_id   BIGINT         NOT NULL DEFAULT 0 COMMENT '销售单ID',
    contract_no      VARCHAR(64)    NOT NULL DEFAULT '' COMMENT '合同编号',
    outbound_id      BIGINT         NOT NULL DEFAULT 0 COMMENT '出库单ID',
    outbound_no      VARCHAR(64)    NOT NULL DEFAULT '' COMMENT '出库单号',
    bind_amount      DECIMAL(18, 2) NOT NULL DEFAULT 0.00 COMMENT '本次绑定金额',
    remark           VARCHAR(1024)  NOT NULL DEFAULT '' COMMENT '备注',
    UNIQUE KEY uk_invoice_bind_id (`invoice_bind_id`),
    KEY idx_invoice_id (`invoice_id`),
    KEY idx_sales_order_id (`sales_order_id`),
    KEY idx_outbound_id (`outbound_id`),
    KEY idx_contract_no (`contract_no`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_general_ci
  ROW_FORMAT = Dynamic
  COMMENT ='发票绑定销售单/出库单关系表';

DROP TABLE IF EXISTS `sales_receipt`;
CREATE TABLE `sales_receipt`
(
    id               INT            NOT NULL AUTO_INCREMENT PRIMARY KEY,
    del_state        INT            NOT NULL DEFAULT 0 COMMENT '0-正常 1-删除',
    del_time         TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '删除时间',
    create_time      TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    receipt_id       BIGINT         NOT NULL COMMENT '分布式唯一ID',
    receipt_no       VARCHAR(64)    NOT NULL COMMENT '收款单号',
    customer_id      BIGINT         NOT NULL DEFAULT 0 COMMENT '客户ID',
    customer_name    VARCHAR(128)   NOT NULL DEFAULT '' COMMENT '客户名称',
    receipt_date     DATE           NOT NULL COMMENT '收款日期',
    receipt_amount   DECIMAL(18, 2) NOT NULL DEFAULT 0.00 COMMENT '收款金额',
    payment_method   TINYINT        NOT NULL DEFAULT 0 COMMENT '收款方式 0-其他 1-现金 2-转账 3-微信 4-支付宝 5-承兑',
    bank_name        VARCHAR(128)   NOT NULL DEFAULT '' COMMENT '收款银行',
    bank_account     VARCHAR(128)   NOT NULL DEFAULT '' COMMENT '收款账号',
    payer_name       VARCHAR(64)    NOT NULL DEFAULT '' COMMENT '付款方',
    maker_user_id    BIGINT         NOT NULL DEFAULT 0 COMMENT '制单人ID',
    maker_user_name  VARCHAR(64)    NOT NULL DEFAULT '' COMMENT '制单人',
    audit_state      TINYINT        NOT NULL DEFAULT 0 COMMENT '审核状态 0-待审核 1-已审核 2-反审核',
    audit_user_id    BIGINT         NOT NULL DEFAULT 0 COMMENT '审核人ID',
    audit_user_name  VARCHAR(64)    NOT NULL DEFAULT '' COMMENT '审核人',
    audit_time       DATETIME                DEFAULT NULL COMMENT '审核时间',
    remark           VARCHAR(1024)  NOT NULL DEFAULT '' COMMENT '备注',
    UNIQUE KEY uk_receipt_id (`receipt_id`),
    UNIQUE KEY uk_receipt_no (`receipt_no`),
    KEY idx_customer_id (`customer_id`),
    KEY idx_customer_name (`customer_name`),
    KEY idx_receipt_date (`receipt_date`),
    KEY idx_audit_state (`audit_state`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_general_ci
  ROW_FORMAT = Dynamic
  COMMENT ='收款主表';

DROP TABLE IF EXISTS `sales_receipt_bind`;
CREATE TABLE `sales_receipt_bind`
(
    id               INT            NOT NULL AUTO_INCREMENT PRIMARY KEY,
    del_state        INT            NOT NULL DEFAULT 0 COMMENT '0-正常 1-删除',
    del_time         TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '删除时间',
    create_time      TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    receipt_bind_id  BIGINT         NOT NULL COMMENT '分布式唯一ID',
    receipt_id       BIGINT         NOT NULL DEFAULT 0 COMMENT '收款单ID',
    receipt_no       VARCHAR(64)    NOT NULL DEFAULT '' COMMENT '收款单号',
    sales_order_id   BIGINT         NOT NULL DEFAULT 0 COMMENT '销售单ID',
    contract_no      VARCHAR(64)    NOT NULL DEFAULT '' COMMENT '合同编号',
    bind_amount      DECIMAL(18, 2) NOT NULL DEFAULT 0.00 COMMENT '本次核销金额',
    discount_amount  DECIMAL(18, 2) NOT NULL DEFAULT 0.00 COMMENT '本次优惠/抹零金额',
    remark           VARCHAR(1024)  NOT NULL DEFAULT '' COMMENT '备注',
    UNIQUE KEY uk_receipt_bind_id (`receipt_bind_id`),
    KEY idx_receipt_id (`receipt_id`),
    KEY idx_sales_order_id (`sales_order_id`),
    KEY idx_contract_no (`contract_no`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_general_ci
  ROW_FORMAT = Dynamic
  COMMENT ='收款核销销售单关系表';

DROP TABLE IF EXISTS `sales_return`;
CREATE TABLE `sales_return`
(
    id                 INT            NOT NULL AUTO_INCREMENT PRIMARY KEY,
    del_state          INT            NOT NULL DEFAULT 0 COMMENT '0-正常 1-删除',
    del_time           TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '删除时间',
    create_time        TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    return_id          BIGINT         NOT NULL COMMENT '分布式唯一ID',
    return_no          VARCHAR(64)    NOT NULL COMMENT '退货单编号',
    related_outbound_id BIGINT        NOT NULL DEFAULT 0 COMMENT '关联出库单ID',
    related_outbound_no VARCHAR(64)   NOT NULL DEFAULT '' COMMENT '关联出库单号',
    customer_id        BIGINT         NOT NULL DEFAULT 0 COMMENT '客户ID',
    customer_name      VARCHAR(128)   NOT NULL DEFAULT '' COMMENT '客户名称',
    return_type        TINYINT        NOT NULL DEFAULT 1 COMMENT '退货类型 1-入库退货 2-出库退货',
    actual_stockin_date DATE                   DEFAULT NULL COMMENT '实际入库日期',
    total_quantity     DECIMAL(18, 4) NOT NULL DEFAULT 0.0000 COMMENT '退货总数量',
    total_amount       DECIMAL(18, 2) NOT NULL DEFAULT 0.00 COMMENT '退货总金额',
    audit_state        TINYINT        NOT NULL DEFAULT 0 COMMENT '审核状态 0-待审核 1-已审核 2-反审核',
    maker_user_id      BIGINT         NOT NULL DEFAULT 0 COMMENT '制单人ID',
    maker_user_name    VARCHAR(64)    NOT NULL DEFAULT '' COMMENT '制单人',
    audit_user_id      BIGINT         NOT NULL DEFAULT 0 COMMENT '审核人ID',
    audit_user_name    VARCHAR(64)    NOT NULL DEFAULT '' COMMENT '审核人',
    audit_time         DATETIME                DEFAULT NULL COMMENT '审核时间',
    remark             VARCHAR(1024)  NOT NULL DEFAULT '' COMMENT '备注',
    UNIQUE KEY uk_return_id (`return_id`),
    UNIQUE KEY uk_return_no (`return_no`),
    KEY idx_customer_id (`customer_id`),
    KEY idx_customer_name (`customer_name`),
    KEY idx_actual_stockin_date (`actual_stockin_date`),
    KEY idx_audit_state (`audit_state`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_general_ci
  ROW_FORMAT = Dynamic
  COMMENT ='物料退货主表';

DROP TABLE IF EXISTS `sales_return_item`;
CREATE TABLE `sales_return_item`
(
    id                 INT            NOT NULL AUTO_INCREMENT PRIMARY KEY,
    del_state          INT            NOT NULL DEFAULT 0 COMMENT '0-正常 1-删除',
    del_time           TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '删除时间',
    create_time        TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    return_item_id     BIGINT         NOT NULL COMMENT '分布式唯一ID',
    return_id          BIGINT         NOT NULL DEFAULT 0 COMMENT '退货单ID',
    return_no          VARCHAR(64)    NOT NULL DEFAULT '' COMMENT '退货单号',
    line_no            INT            NOT NULL DEFAULT 1 COMMENT '行号',
    sales_order_id     BIGINT         NOT NULL DEFAULT 0 COMMENT '销售单ID',
    sales_order_item_id BIGINT        NOT NULL DEFAULT 0 COMMENT '销售单明细ID',
    product_id         BIGINT         NOT NULL DEFAULT 0 COMMENT '产品ID',
    product_code       VARCHAR(64)    NOT NULL DEFAULT '' COMMENT '产品编码',
    product_name       VARCHAR(128)   NOT NULL DEFAULT '' COMMENT '物料/产品名称',
    product_spec       VARCHAR(128)   NOT NULL DEFAULT '' COMMENT '规格',
    warehouse_name     VARCHAR(128)   NOT NULL DEFAULT '' COMMENT '退货仓库名称',
    unit_name          VARCHAR(32)    NOT NULL DEFAULT '' COMMENT '单位',
    quantity           DECIMAL(18, 4) NOT NULL DEFAULT 0.0000 COMMENT '退货数量',
    price              DECIMAL(18, 4) NOT NULL DEFAULT 0.0000 COMMENT '单价',
    amount             DECIMAL(18, 2) NOT NULL DEFAULT 0.00 COMMENT '金额',
    remark             VARCHAR(1024)  NOT NULL DEFAULT '' COMMENT '备注',
    UNIQUE KEY uk_return_item_id (`return_item_id`),
    UNIQUE KEY uk_return_line (`return_id`, `line_no`),
    KEY idx_return_no (`return_no`),
    KEY idx_sales_order_id (`sales_order_id`),
    KEY idx_sales_order_item_id (`sales_order_item_id`),
    KEY idx_product_id (`product_id`),
    KEY idx_product_name (`product_name`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_general_ci
  ROW_FORMAT = Dynamic
  COMMENT ='物料退货明细表';

DROP TABLE IF EXISTS `sales_customer_product_price`;
CREATE TABLE `sales_customer_product_price`
(
    id                        INT            NOT NULL AUTO_INCREMENT PRIMARY KEY,
    del_state                 INT            NOT NULL DEFAULT 0 COMMENT '0-正常 1-删除',
    del_time                  TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '删除时间',
    create_time               TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    customer_product_price_id BIGINT         NOT NULL COMMENT '分布式唯一ID',
    customer_id               BIGINT         NOT NULL DEFAULT 0 COMMENT '客户ID',
    customer_name             VARCHAR(128)   NOT NULL DEFAULT '' COMMENT '客户名称',
    product_id                BIGINT         NOT NULL DEFAULT 0 COMMENT '产品ID',
    product_name              VARCHAR(128)   NOT NULL DEFAULT '' COMMENT '产品名称',
    product_spec              VARCHAR(128)   NOT NULL DEFAULT '' COMMENT '产品规格',
    tax_rate                  DECIMAL(10, 2) NOT NULL DEFAULT 0.00 COMMENT '税率',
    price                     DECIMAL(18, 4) NOT NULL DEFAULT 0.0000 COMMENT '未税单价',
    tax_price                 DECIMAL(18, 4) NOT NULL DEFAULT 0.0000 COMMENT '含税单价',
    last_sales_order_id       BIGINT         NOT NULL DEFAULT 0 COMMENT '最近成交销售单ID',
    last_contract_no          VARCHAR(64)    NOT NULL DEFAULT '' COMMENT '最近成交合同编号',
    effective_date            DATE                    DEFAULT NULL COMMENT '生效日期',
    expire_date               DATE                    DEFAULT NULL COMMENT '失效日期',
    price_source              TINYINT        NOT NULL DEFAULT 1 COMMENT '价格来源 1-手工维护 2-最近成交价回写',
    remark                    VARCHAR(1024)  NOT NULL DEFAULT '' COMMENT '备注',
    UNIQUE KEY uk_customer_product_price_id (`customer_product_price_id`),
    UNIQUE KEY uk_customer_product (`customer_id`, `product_id`),
    KEY idx_customer_name (`customer_name`),
    KEY idx_product_name (`product_name`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_general_ci
  ROW_FORMAT = Dynamic
  COMMENT ='客户产品价格表';

DROP TABLE IF EXISTS `sales_feedback`;
CREATE TABLE `sales_feedback`
(
    id                          INT            NOT NULL AUTO_INCREMENT PRIMARY KEY,
    del_state                   INT            NOT NULL DEFAULT 0 COMMENT '0-正常 1-删除',
    del_time                    TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '删除时间',
    create_time                 TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    feedback_id                 BIGINT         NOT NULL COMMENT '分布式唯一ID',
    feedback_token              VARCHAR(128)   NOT NULL COMMENT '反馈链接令牌',
    sales_order_id              BIGINT         NOT NULL DEFAULT 0 COMMENT '销售单ID',
    contract_no                 VARCHAR(64)    NOT NULL DEFAULT '' COMMENT '合同编号',
    customer_id                 BIGINT         NOT NULL DEFAULT 0 COMMENT '客户ID',
    customer_name               VARCHAR(128)   NOT NULL DEFAULT '' COMMENT '客户名称',
    complaint_count             INT            NOT NULL DEFAULT 0 COMMENT '投诉记录数量',
    complaint_content           VARCHAR(1024)  NOT NULL DEFAULT '' COMMENT '投诉内容',
    score_product_quality       TINYINT        NOT NULL DEFAULT 0 COMMENT '产品质量评分 1-5',
    score_delivery_response     TINYINT        NOT NULL DEFAULT 0 COMMENT '交付和响应时间评分 1-5',
    score_pre_after_service     TINYINT        NOT NULL DEFAULT 0 COMMENT '售前与售后服务评分 1-5',
    score_price_performance     TINYINT        NOT NULL DEFAULT 0 COMMENT '价格和性价比评分 1-5',
    score_customization         TINYINT        NOT NULL DEFAULT 0 COMMENT '定制化能力评分 1-5',
    score_cooperation_relation  TINYINT        NOT NULL DEFAULT 0 COMMENT '合作关系评分 1-5',
    overall_score               DECIMAL(5, 2)  NOT NULL DEFAULT 0.00 COMMENT '总体评分',
    order_date                  DATE                    DEFAULT NULL COMMENT '订单日期',
    delivery_date               DATE                    DEFAULT NULL COMMENT '交货日期',
    drawer_user_id              BIGINT         NOT NULL DEFAULT 0 COMMENT '开单员ID',
    drawer_user_name            VARCHAR(64)    NOT NULL DEFAULT '' COMMENT '开单员',
    submit_state                TINYINT        NOT NULL DEFAULT 0 COMMENT '提交状态 0-未填写 1-已填写',
    submit_time                 DATETIME                DEFAULT NULL COMMENT '提交时间',
    remark                      VARCHAR(1024)  NOT NULL DEFAULT '' COMMENT '备注',
    UNIQUE KEY uk_feedback_id (`feedback_id`),
    UNIQUE KEY uk_feedback_token (`feedback_token`),
    UNIQUE KEY uk_feedback_sales_order (`sales_order_id`),
    KEY idx_customer_id (`customer_id`),
    KEY idx_customer_name (`customer_name`),
    KEY idx_contract_no (`contract_no`),
    KEY idx_submit_state (`submit_state`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_general_ci
  ROW_FORMAT = Dynamic
  COMMENT ='客户反馈表';

DROP TABLE IF EXISTS `sales_after_sale`;
CREATE TABLE `sales_after_sale`
(
    id                 INT            NOT NULL AUTO_INCREMENT PRIMARY KEY,
    del_state          INT            NOT NULL DEFAULT 0 COMMENT '0-正常 1-删除',
    del_time           TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '删除时间',
    create_time        TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    after_sale_id      BIGINT         NOT NULL COMMENT '分布式唯一ID',
    after_sale_no      VARCHAR(64)    NOT NULL COMMENT '售后单号',
    sales_order_id     BIGINT         NOT NULL DEFAULT 0 COMMENT '销售单ID',
    contract_no        VARCHAR(64)    NOT NULL DEFAULT '' COMMENT '合同编号',
    customer_id        BIGINT         NOT NULL DEFAULT 0 COMMENT '客户ID',
    customer_name      VARCHAR(128)   NOT NULL DEFAULT '' COMMENT '客户名称',
    issue_type         TINYINT        NOT NULL DEFAULT 0 COMMENT '问题类型 0-其他 1-投诉 2-退货 3-补发 4-质量问题',
    issue_title        VARCHAR(255)   NOT NULL DEFAULT '' COMMENT '问题标题',
    issue_content      VARCHAR(1024)  NOT NULL DEFAULT '' COMMENT '问题描述',
    process_state      TINYINT        NOT NULL DEFAULT 0 COMMENT '处理状态 0-待处理 1-处理中 2-已完成 3-已关闭',
    creator_user_id    BIGINT         NOT NULL DEFAULT 0 COMMENT '创建人ID',
    creator_user_name  VARCHAR(64)    NOT NULL DEFAULT '' COMMENT '创建人',
    handler_user_id    BIGINT         NOT NULL DEFAULT 0 COMMENT '处理人ID',
    handler_user_name  VARCHAR(64)    NOT NULL DEFAULT '' COMMENT '处理人',
    close_time         DATETIME                DEFAULT NULL COMMENT '关闭时间',
    remark             VARCHAR(1024)  NOT NULL DEFAULT '' COMMENT '备注',
    UNIQUE KEY uk_after_sale_id (`after_sale_id`),
    UNIQUE KEY uk_after_sale_no (`after_sale_no`),
    KEY idx_sales_order_id (`sales_order_id`),
    KEY idx_contract_no (`contract_no`),
    KEY idx_customer_id (`customer_id`),
    KEY idx_process_state (`process_state`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_general_ci
  ROW_FORMAT = Dynamic
  COMMENT ='售后服务表';

/* =========================
   以下 3 张表为“可选汇总表”
   数据量不大时，可以不落库，直接用核心表统计。
   ========================= */

DROP TABLE IF EXISTS `sales_report_order_month`;
CREATE TABLE `sales_report_order_month`
(
    id                     INT            NOT NULL AUTO_INCREMENT PRIMARY KEY,
    del_state              INT            NOT NULL DEFAULT 0 COMMENT '0-正常 1-删除',
    del_time               TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '删除时间',
    create_time            TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    report_order_month_id  BIGINT         NOT NULL COMMENT '分布式唯一ID',
    stat_year              INT            NOT NULL DEFAULT 0 COMMENT '统计年份',
    stat_month             INT            NOT NULL DEFAULT 0 COMMENT '统计月份',
    total_product_quantity DECIMAL(18, 4) NOT NULL DEFAULT 0.0000 COMMENT '累计产品数量',
    total_ship_quantity    DECIMAL(18, 4) NOT NULL DEFAULT 0.0000 COMMENT '累计发货数量',
    total_order_count      INT            NOT NULL DEFAULT 0 COMMENT '累计订单量',
    total_amount           DECIMAL(18, 2) NOT NULL DEFAULT 0.00 COMMENT '总金额',
    remark                 VARCHAR(1024)  NOT NULL DEFAULT '' COMMENT '备注',
    UNIQUE KEY uk_report_order_month_id (`report_order_month_id`),
    UNIQUE KEY uk_order_month (`stat_year`, `stat_month`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_general_ci
  ROW_FORMAT = Dynamic
  COMMENT ='订单分析月汇总表';

DROP TABLE IF EXISTS `sales_report_customer_month`;
CREATE TABLE `sales_report_customer_month`
(
    id                      INT            NOT NULL AUTO_INCREMENT PRIMARY KEY,
    del_state               INT            NOT NULL DEFAULT 0 COMMENT '0-正常 1-删除',
    del_time                TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '删除时间',
    create_time             TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    report_customer_month_id BIGINT        NOT NULL COMMENT '分布式唯一ID',
    stat_year               INT            NOT NULL DEFAULT 0 COMMENT '统计年份',
    stat_month              INT            NOT NULL DEFAULT 0 COMMENT '统计月份',
    customer_id             BIGINT         NOT NULL DEFAULT 0 COMMENT '客户ID',
    customer_name           VARCHAR(128)   NOT NULL DEFAULT '' COMMENT '客户名称',
    total_order_quantity    DECIMAL(18, 4) NOT NULL DEFAULT 0.0000 COMMENT '累计订单数量',
    total_ship_quantity     DECIMAL(18, 4) NOT NULL DEFAULT 0.0000 COMMENT '累计发货数量',
    total_order_count       INT            NOT NULL DEFAULT 0 COMMENT '累计订单量',
    total_amount            DECIMAL(18, 2) NOT NULL DEFAULT 0.00 COMMENT '总金额',
    closing_debt_amount     DECIMAL(18, 2) NOT NULL DEFAULT 0.00 COMMENT '结存欠款',
    current_debt_amount     DECIMAL(18, 2) NOT NULL DEFAULT 0.00 COMMENT '当前欠款',
    remark                  VARCHAR(1024)  NOT NULL DEFAULT '' COMMENT '备注',
    UNIQUE KEY uk_report_customer_month_id (`report_customer_month_id`),
    UNIQUE KEY uk_customer_month (`stat_year`, `stat_month`, `customer_id`),
    KEY idx_customer_name (`customer_name`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_general_ci
  ROW_FORMAT = Dynamic
  COMMENT ='客户分析月汇总表';

DROP TABLE IF EXISTS `sales_report_customer_day`;
CREATE TABLE `sales_report_customer_day`
(
    id                      INT            NOT NULL AUTO_INCREMENT PRIMARY KEY,
    del_state               INT            NOT NULL DEFAULT 0 COMMENT '0-正常 1-删除',
    del_time                TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '删除时间',
    create_time             TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    report_customer_day_id  BIGINT         NOT NULL COMMENT '分布式唯一ID',
    stat_date               DATE           NOT NULL COMMENT '统计日期',
    customer_id             BIGINT         NOT NULL DEFAULT 0 COMMENT '客户ID',
    customer_name           VARCHAR(128)   NOT NULL DEFAULT '' COMMENT '客户名称',
    water_workshop_quantity DECIMAL(18, 4) NOT NULL DEFAULT 0.0000 COMMENT '水性车间数量',
    oil_workshop_quantity   DECIMAL(18, 4) NOT NULL DEFAULT 0.0000 COMMENT '油性车间数量',
    other_quantity          DECIMAL(18, 4) NOT NULL DEFAULT 0.0000 COMMENT '其他数量',
    received_amount         DECIMAL(18, 2) NOT NULL DEFAULT 0.00 COMMENT '已收款金额',
    unpaid_amount           DECIMAL(18, 2) NOT NULL DEFAULT 0.00 COMMENT '未收款金额',
    remark                  VARCHAR(1024)  NOT NULL DEFAULT '' COMMENT '备注',
    UNIQUE KEY uk_report_customer_day_id (`report_customer_day_id`),
    UNIQUE KEY uk_customer_day (`stat_date`, `customer_id`),
    KEY idx_customer_name (`customer_name`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_general_ci
  ROW_FORMAT = Dynamic
  COMMENT ='销售报表日报汇总表';
