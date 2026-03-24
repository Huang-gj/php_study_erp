

SET NAMES utf8mb4;

DROP TABLE IF EXISTS `purchase_supplier_category`;
CREATE TABLE `purchase_supplier_category`
(
    id                     INT            NOT NULL AUTO_INCREMENT PRIMARY KEY,
    del_state              INT            NOT NULL DEFAULT 0 COMMENT '0-正常 1-删除',
    del_time               TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '删除时间',
    create_time            TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    supplier_category_id   BIGINT         NOT NULL COMMENT '分布式唯一ID',
    parent_category_id     BIGINT         NOT NULL DEFAULT 0 COMMENT '父级分类ID，0-顶级',
    parent_category_name   VARCHAR(128)   NOT NULL DEFAULT '' COMMENT '父级分类名称',
    category_code          VARCHAR(64)    NOT NULL DEFAULT '' COMMENT '分类编号',
    category_name          VARCHAR(128)   NOT NULL DEFAULT '' COMMENT '分类名称',
    category_level         TINYINT        NOT NULL DEFAULT 1 COMMENT '分类层级 1-一级 2-二级 3-三级',
    sort_no                INT            NOT NULL DEFAULT 0 COMMENT '排序号',
    category_state         TINYINT        NOT NULL DEFAULT 1 COMMENT '状态 0-停用 1-启用',
    remark                 VARCHAR(1024)  NOT NULL DEFAULT '' COMMENT '备注',
    UNIQUE KEY uk_supplier_category_id (`supplier_category_id`),
    UNIQUE KEY uk_category_code (`category_code`),
    KEY idx_parent_category_id (`parent_category_id`),
    KEY idx_category_name (`category_name`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_general_ci
  ROW_FORMAT = Dynamic
  COMMENT ='供应商分类表';

DROP TABLE IF EXISTS `purchase_supplier`;
CREATE TABLE `purchase_supplier`
(
    id                      INT            NOT NULL AUTO_INCREMENT PRIMARY KEY,
    del_state               INT            NOT NULL DEFAULT 0 COMMENT '0-正常 1-删除',
    del_time                TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '删除时间',
    create_time             TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    supplier_id             BIGINT         NOT NULL COMMENT '分布式唯一ID',
    supplier_code           VARCHAR(64)    NOT NULL DEFAULT '' COMMENT '供应商代码',
    supplier_name           VARCHAR(128)   NOT NULL DEFAULT '' COMMENT '供应商名称',
    supplier_short_name     VARCHAR(128)   NOT NULL DEFAULT '' COMMENT '供应商简称',
    supplier_category_id    BIGINT         NOT NULL DEFAULT 0 COMMENT '供应商分类ID',
    supplier_category_name  VARCHAR(128)   NOT NULL DEFAULT '' COMMENT '供应商分类名称',
    contact_name            VARCHAR(64)    NOT NULL DEFAULT '' COMMENT '联系人',
    mobile_phone            VARCHAR(20)    NOT NULL DEFAULT '' COMMENT '手机号',
    telephone               VARCHAR(20)    NOT NULL DEFAULT '' COMMENT '电话',
    fax                     VARCHAR(32)    NOT NULL DEFAULT '' COMMENT '传真',
    email                   VARCHAR(128)   NOT NULL DEFAULT '' COMMENT '邮箱',
    website                 VARCHAR(255)   NOT NULL DEFAULT '' COMMENT '官网地址',
    province                VARCHAR(64)    NOT NULL DEFAULT '' COMMENT '省',
    city                    VARCHAR(64)    NOT NULL DEFAULT '' COMMENT '市',
    district                VARCHAR(64)    NOT NULL DEFAULT '' COMMENT '区',
    detail_address          VARCHAR(255)   NOT NULL DEFAULT '' COMMENT '详细地址',
    principal_name          VARCHAR(64)    NOT NULL DEFAULT '' COMMENT '负责人',
    maker_user_id           BIGINT         NOT NULL DEFAULT 0 COMMENT '制单人ID',
    maker_user_name         VARCHAR(64)    NOT NULL DEFAULT '' COMMENT '制单人',
    maker_department_name   VARCHAR(128)   NOT NULL DEFAULT '' COMMENT '制单人所属部门',
    settlement_type         TINYINT        NOT NULL DEFAULT 0 COMMENT '结算方式 0-其他 1-现结 2-月结 3-预付 4-货到付款',
    payment_term_days       INT            NOT NULL DEFAULT 0 COMMENT '账期天数',
    tax_no                  VARCHAR(64)    NOT NULL DEFAULT '' COMMENT '税号',
    invoice_title           VARCHAR(255)   NOT NULL DEFAULT '' COMMENT '发票抬头',
    bank_name               VARCHAR(128)   NOT NULL DEFAULT '' COMMENT '开户银行',
    bank_account            VARCHAR(128)   NOT NULL DEFAULT '' COMMENT '银行账号',
    supplier_state          TINYINT        NOT NULL DEFAULT 1 COMMENT '状态 0-停用 1-启用',
    remark                  VARCHAR(1024)  NOT NULL DEFAULT '' COMMENT '备注',
    UNIQUE KEY uk_supplier_id (`supplier_id`),
    UNIQUE KEY uk_supplier_code (`supplier_code`),
    KEY idx_supplier_name (`supplier_name`),
    KEY idx_supplier_category_id (`supplier_category_id`),
    KEY idx_contact_name (`contact_name`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_general_ci
  ROW_FORMAT = Dynamic
  COMMENT ='供应商主表';

DROP TABLE IF EXISTS `purchase_supplier_contact`;
CREATE TABLE `purchase_supplier_contact`
(
    id                     INT            NOT NULL AUTO_INCREMENT PRIMARY KEY,
    del_state              INT            NOT NULL DEFAULT 0 COMMENT '0-正常 1-删除',
    del_time               TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '删除时间',
    create_time            TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    supplier_contact_id    BIGINT         NOT NULL COMMENT '分布式唯一ID',
    supplier_id            BIGINT         NOT NULL DEFAULT 0 COMMENT '供应商ID',
    supplier_name          VARCHAR(128)   NOT NULL DEFAULT '' COMMENT '供应商名称',
    contact_name           VARCHAR(64)    NOT NULL DEFAULT '' COMMENT '联系人',
    position_name          VARCHAR(64)    NOT NULL DEFAULT '' COMMENT '职位',
    mobile_phone           VARCHAR(20)    NOT NULL DEFAULT '' COMMENT '手机号',
    telephone              VARCHAR(20)    NOT NULL DEFAULT '' COMMENT '电话',
    email                  VARCHAR(128)   NOT NULL DEFAULT '' COMMENT '邮箱',
    wechat                 VARCHAR(64)    NOT NULL DEFAULT '' COMMENT '微信',
    qq                     VARCHAR(32)    NOT NULL DEFAULT '' COMMENT 'QQ',
    is_default             TINYINT        NOT NULL DEFAULT 0 COMMENT '是否默认联系人 0-否 1-是',
    remark                 VARCHAR(1024)  NOT NULL DEFAULT '' COMMENT '备注',
    UNIQUE KEY uk_supplier_contact_id (`supplier_contact_id`),
    KEY idx_supplier_id (`supplier_id`),
    KEY idx_contact_name (`contact_name`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_general_ci
  ROW_FORMAT = Dynamic
  COMMENT ='供应商联系人表';

DROP TABLE IF EXISTS `purchase_request`;
CREATE TABLE `purchase_request`
(
    id                     INT            NOT NULL AUTO_INCREMENT PRIMARY KEY,
    del_state              INT            NOT NULL DEFAULT 0 COMMENT '0-正常 1-删除',
    del_time               TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '删除时间',
    create_time            TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    purchase_request_id    BIGINT         NOT NULL COMMENT '分布式唯一ID',
    request_no             VARCHAR(64)    NOT NULL DEFAULT '' COMMENT '请购单号',
    request_date           DATE           NOT NULL COMMENT '请购日期',
    demand_date            DATE                    DEFAULT NULL COMMENT '需求日期',
    audit_state            TINYINT        NOT NULL DEFAULT 0 COMMENT '审核状态 0-待审核 1-已审核 2-反审核 3-作废',
    request_state          TINYINT        NOT NULL DEFAULT 0 COMMENT '单据状态 0-待处理 1-部分下单 2-已下单 3-关闭',
    item_count             INT            NOT NULL DEFAULT 0 COMMENT '明细行数',
    total_quantity         DECIMAL(18, 4) NOT NULL DEFAULT 0.0000 COMMENT '总数量',
    maker_user_id          BIGINT         NOT NULL DEFAULT 0 COMMENT '制单人ID',
    maker_user_name        VARCHAR(64)    NOT NULL DEFAULT '' COMMENT '制单人',
    maker_department_name  VARCHAR(128)   NOT NULL DEFAULT '' COMMENT '制单部门',
    audit_user_id          BIGINT         NOT NULL DEFAULT 0 COMMENT '审核人ID',
    audit_user_name        VARCHAR(64)    NOT NULL DEFAULT '' COMMENT '审核人',
    audit_time             DATETIME                DEFAULT NULL COMMENT '审核时间',
    source_type            TINYINT        NOT NULL DEFAULT 0 COMMENT '来源类型 0-手工新增 1-销售需求 2-库存预警',
    source_no              VARCHAR(64)    NOT NULL DEFAULT '' COMMENT '来源单号',
    remark                 VARCHAR(1024)  NOT NULL DEFAULT '' COMMENT '备注',
    UNIQUE KEY uk_purchase_request_id (`purchase_request_id`),
    UNIQUE KEY uk_request_no (`request_no`),
    KEY idx_request_date (`request_date`),
    KEY idx_demand_date (`demand_date`),
    KEY idx_maker_user_id (`maker_user_id`),
    KEY idx_audit_state (`audit_state`),
    KEY idx_request_state (`request_state`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_general_ci
  ROW_FORMAT = Dynamic
  COMMENT ='请购单主表';

DROP TABLE IF EXISTS `purchase_request_item`;
CREATE TABLE `purchase_request_item`
(
    id                        INT            NOT NULL AUTO_INCREMENT PRIMARY KEY,
    del_state                 INT            NOT NULL DEFAULT 0 COMMENT '0-正常 1-删除',
    del_time                  TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '删除时间',
    create_time               TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    purchase_request_item_id  BIGINT         NOT NULL COMMENT '分布式唯一ID',
    purchase_request_id       BIGINT         NOT NULL DEFAULT 0 COMMENT '请购单ID',
    request_no                VARCHAR(64)    NOT NULL DEFAULT '' COMMENT '请购单号',
    line_no                   INT            NOT NULL DEFAULT 1 COMMENT '行号',
    material_id               BIGINT         NOT NULL DEFAULT 0 COMMENT '物料ID，可指向共享物料主表',
    material_code             VARCHAR(64)    NOT NULL DEFAULT '' COMMENT '存货编码/物料代码',
    material_name             VARCHAR(128)   NOT NULL DEFAULT '' COMMENT '物料名称',
    drawing_no                VARCHAR(128)   NOT NULL DEFAULT '' COMMENT '图号',
    material_spec             VARCHAR(128)   NOT NULL DEFAULT '' COMMENT '规格型号',
    unit_name                 VARCHAR(32)    NOT NULL DEFAULT '' COMMENT '单位',
    quantity                  DECIMAL(18, 4) NOT NULL DEFAULT 0.0000 COMMENT '请购数量',
    ordered_quantity          DECIMAL(18, 4) NOT NULL DEFAULT 0.0000 COMMENT '已下单数量',
    arrived_quantity          DECIMAL(18, 4) NOT NULL DEFAULT 0.0000 COMMENT '已到货数量',
    demand_date               DATE                    DEFAULT NULL COMMENT '需求日期',
    expected_arrival_date     DATE                    DEFAULT NULL COMMENT '预计到货日期',
    item_state                TINYINT        NOT NULL DEFAULT 0 COMMENT '明细状态 0-未下单 1-部分下单 2-已下单 3-关闭',
    maker_user_id             BIGINT         NOT NULL DEFAULT 0 COMMENT '制单人ID',
    maker_user_name           VARCHAR(64)    NOT NULL DEFAULT '' COMMENT '制单人',
    maker_department_name     VARCHAR(128)   NOT NULL DEFAULT '' COMMENT '制单部门',
    remark                    VARCHAR(1024)  NOT NULL DEFAULT '' COMMENT '备注',
    UNIQUE KEY uk_purchase_request_item_id (`purchase_request_item_id`),
    UNIQUE KEY uk_request_line (`purchase_request_id`, `line_no`),
    KEY idx_material_id (`material_id`),
    KEY idx_material_code (`material_code`),
    KEY idx_material_name (`material_name`),
    KEY idx_demand_date (`demand_date`),
    KEY idx_expected_arrival_date (`expected_arrival_date`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_general_ci
  ROW_FORMAT = Dynamic
  COMMENT ='请购单明细表';

DROP TABLE IF EXISTS `purchase_order`;
CREATE TABLE `purchase_order`
(
    id                     INT            NOT NULL AUTO_INCREMENT PRIMARY KEY,
    del_state              INT            NOT NULL DEFAULT 0 COMMENT '0-正常 1-删除',
    del_time               TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '删除时间',
    create_time            TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    purchase_order_id      BIGINT         NOT NULL COMMENT '分布式唯一ID',
    purchase_order_no      VARCHAR(64)    NOT NULL DEFAULT '' COMMENT '采购单号/采购合同号',
    supplier_id            BIGINT         NOT NULL DEFAULT 0 COMMENT '供应商ID',
    supplier_name          VARCHAR(128)   NOT NULL DEFAULT '' COMMENT '供应商名称',
    contract_date          DATE           NOT NULL COMMENT '合同日期',
    delivery_date          DATE                    DEFAULT NULL COMMENT '交货日期',
    expected_arrival_date  DATE                    DEFAULT NULL COMMENT '预计到货日期',
    audit_state            TINYINT        NOT NULL DEFAULT 0 COMMENT '审核状态 0-待审核 1-审核通过 2-反审核 3-作废',
    order_state            TINYINT        NOT NULL DEFAULT 0 COMMENT '单据状态 0-待审核 1-已审核 2-部分到货 3-已到货 4-部分开票 5-已开票 6-已完成 7-已关闭',
    arrival_state          TINYINT        NOT NULL DEFAULT 0 COMMENT '到货状态 0-未到货 1-部分到货 2-全部到货',
    invoice_state          TINYINT        NOT NULL DEFAULT 0 COMMENT '开票状态 0-未开票 1-部分开票 2-全部开票',
    payment_state          TINYINT        NOT NULL DEFAULT 0 COMMENT '付款状态 0-未付款 1-部分付款 2-已付款',
    source_type            TINYINT        NOT NULL DEFAULT 0 COMMENT '来源类型 0-手工新增 1-请购单生成',
    source_request_id      BIGINT         NOT NULL DEFAULT 0 COMMENT '来源请购单ID',
    source_request_no      VARCHAR(64)    NOT NULL DEFAULT '' COMMENT '来源请购单号',
    item_count             INT            NOT NULL DEFAULT 0 COMMENT '明细行数',
    total_quantity         DECIMAL(18, 4) NOT NULL DEFAULT 0.0000 COMMENT '总数量',
    total_amount           DECIMAL(18, 2) NOT NULL DEFAULT 0.00 COMMENT '未税总金额',
    total_tax_amount       DECIMAL(18, 2) NOT NULL DEFAULT 0.00 COMMENT '含税总金额',
    arrived_quantity       DECIMAL(18, 4) NOT NULL DEFAULT 0.0000 COMMENT '累计到货数量',
    invoiced_amount        DECIMAL(18, 2) NOT NULL DEFAULT 0.00 COMMENT '累计开票金额',
    paid_amount            DECIMAL(18, 2) NOT NULL DEFAULT 0.00 COMMENT '累计付款金额',
    unpaid_amount          DECIMAL(18, 2) NOT NULL DEFAULT 0.00 COMMENT '未付款金额',
    maker_user_id          BIGINT         NOT NULL DEFAULT 0 COMMENT '制单人ID',
    maker_user_name        VARCHAR(64)    NOT NULL DEFAULT '' COMMENT '制单人',
    audit_user_id          BIGINT         NOT NULL DEFAULT 0 COMMENT '审核人ID',
    audit_user_name        VARCHAR(64)    NOT NULL DEFAULT '' COMMENT '审核人',
    audit_time             DATETIME                DEFAULT NULL COMMENT '审核时间',
    print_count            INT            NOT NULL DEFAULT 0 COMMENT '打印次数',
    remark                 VARCHAR(1024)  NOT NULL DEFAULT '' COMMENT '备注',
    UNIQUE KEY uk_purchase_order_id (`purchase_order_id`),
    UNIQUE KEY uk_purchase_order_no (`purchase_order_no`),
    KEY idx_supplier_id (`supplier_id`),
    KEY idx_supplier_name (`supplier_name`),
    KEY idx_contract_date (`contract_date`),
    KEY idx_delivery_date (`delivery_date`),
    KEY idx_audit_state (`audit_state`),
    KEY idx_order_state (`order_state`),
    KEY idx_arrival_state (`arrival_state`),
    KEY idx_invoice_state (`invoice_state`),
    KEY idx_payment_state (`payment_state`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_general_ci
  ROW_FORMAT = Dynamic
  COMMENT ='采购单主表';

DROP TABLE IF EXISTS `purchase_order_item`;
CREATE TABLE `purchase_order_item`
(
    id                        INT            NOT NULL AUTO_INCREMENT PRIMARY KEY,
    del_state                 INT            NOT NULL DEFAULT 0 COMMENT '0-正常 1-删除',
    del_time                  TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '删除时间',
    create_time               TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    purchase_order_item_id    BIGINT         NOT NULL COMMENT '分布式唯一ID',
    purchase_order_id         BIGINT         NOT NULL DEFAULT 0 COMMENT '采购单ID',
    purchase_order_no         VARCHAR(64)    NOT NULL DEFAULT '' COMMENT '采购单号',
    line_no                   INT            NOT NULL DEFAULT 1 COMMENT '行号',
    supplier_id               BIGINT         NOT NULL DEFAULT 0 COMMENT '供应商ID',
    supplier_name             VARCHAR(128)   NOT NULL DEFAULT '' COMMENT '供应商名称',
    request_item_id           BIGINT         NOT NULL DEFAULT 0 COMMENT '来源请购明细ID',
    request_no                VARCHAR(64)    NOT NULL DEFAULT '' COMMENT '来源请购单号',
    material_id               BIGINT         NOT NULL DEFAULT 0 COMMENT '物料ID，可指向共享物料主表',
    material_code             VARCHAR(64)    NOT NULL DEFAULT '' COMMENT '存货编码',
    material_name             VARCHAR(128)   NOT NULL DEFAULT '' COMMENT '物料名称',
    material_spec             VARCHAR(128)   NOT NULL DEFAULT '' COMMENT '规格型号',
    unit_name                 VARCHAR(32)    NOT NULL DEFAULT '' COMMENT '单位',
    quantity                  DECIMAL(18, 4) NOT NULL DEFAULT 0.0000 COMMENT '订货数量',
    arrived_quantity          DECIMAL(18, 4) NOT NULL DEFAULT 0.0000 COMMENT '到货数量',
    invoiced_quantity         DECIMAL(18, 4) NOT NULL DEFAULT 0.0000 COMMENT '已开票数量',
    tax_rate                  DECIMAL(10, 2) NOT NULL DEFAULT 0.00 COMMENT '税率',
    price                     DECIMAL(18, 4) NOT NULL DEFAULT 0.0000 COMMENT '不含税单价',
    tax_price                 DECIMAL(18, 4) NOT NULL DEFAULT 0.0000 COMMENT '含税单价',
    amount                    DECIMAL(18, 2) NOT NULL DEFAULT 0.00 COMMENT '不含税金额',
    tax_amount                DECIMAL(18, 2) NOT NULL DEFAULT 0.00 COMMENT '含税金额',
    demand_date               DATE                    DEFAULT NULL COMMENT '需求日期',
    request_create_date       DATE                    DEFAULT NULL COMMENT '请购创建日期',
    expected_arrival_date     DATE                    DEFAULT NULL COMMENT '预计到货日期',
    requester_user_id         BIGINT         NOT NULL DEFAULT 0 COMMENT '请购人ID',
    requester_user_name       VARCHAR(64)    NOT NULL DEFAULT '' COMMENT '请购人',
    item_state                TINYINT        NOT NULL DEFAULT 0 COMMENT '明细状态 0-未到货 1-部分到货 2-已到货 3-关闭',
    remark                    VARCHAR(1024)  NOT NULL DEFAULT '' COMMENT '备注',
    UNIQUE KEY uk_purchase_order_item_id (`purchase_order_item_id`),
    UNIQUE KEY uk_purchase_order_line (`purchase_order_id`, `line_no`),
    KEY idx_purchase_order_no (`purchase_order_no`),
    KEY idx_material_id (`material_id`),
    KEY idx_material_code (`material_code`),
    KEY idx_material_name (`material_name`),
    KEY idx_request_item_id (`request_item_id`),
    KEY idx_expected_arrival_date (`expected_arrival_date`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_general_ci
  ROW_FORMAT = Dynamic
  COMMENT ='采购单明细表';

DROP TABLE IF EXISTS `purchase_receipt`;
CREATE TABLE `purchase_receipt`
(
    id                     INT            NOT NULL AUTO_INCREMENT PRIMARY KEY,
    del_state              INT            NOT NULL DEFAULT 0 COMMENT '0-正常 1-删除',
    del_time               TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '删除时间',
    create_time            TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    purchase_receipt_id    BIGINT         NOT NULL COMMENT '分布式唯一ID',
    receipt_no             VARCHAR(64)    NOT NULL DEFAULT '' COMMENT '到货单号/收货单号',
    supplier_id            BIGINT         NOT NULL DEFAULT 0 COMMENT '供应商ID',
    supplier_name          VARCHAR(128)   NOT NULL DEFAULT '' COMMENT '供应商名称',
    receipt_date           DATE           NOT NULL COMMENT '到货日期',
    audit_state            TINYINT        NOT NULL DEFAULT 0 COMMENT '审核状态 0-待审核 1-已审核 2-反审核 3-作废',
    receipt_state          TINYINT        NOT NULL DEFAULT 0 COMMENT '单据状态 0-待处理 1-部分入库 2-已完成',
    item_count             INT            NOT NULL DEFAULT 0 COMMENT '明细行数',
    total_quantity         DECIMAL(18, 4) NOT NULL DEFAULT 0.0000 COMMENT '总到货数量',
    total_amount           DECIMAL(18, 2) NOT NULL DEFAULT 0.00 COMMENT '总金额',
    maker_user_id          BIGINT         NOT NULL DEFAULT 0 COMMENT '制单人ID',
    maker_user_name        VARCHAR(64)    NOT NULL DEFAULT '' COMMENT '制单人',
    audit_user_id          BIGINT         NOT NULL DEFAULT 0 COMMENT '审核人ID',
    audit_user_name        VARCHAR(64)    NOT NULL DEFAULT '' COMMENT '审核人',
    audit_time             DATETIME                DEFAULT NULL COMMENT '审核时间',
    remark                 VARCHAR(1024)  NOT NULL DEFAULT '' COMMENT '备注',
    UNIQUE KEY uk_purchase_receipt_id (`purchase_receipt_id`),
    UNIQUE KEY uk_receipt_no (`receipt_no`),
    KEY idx_supplier_id (`supplier_id`),
    KEY idx_supplier_name (`supplier_name`),
    KEY idx_receipt_date (`receipt_date`),
    KEY idx_audit_state (`audit_state`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_general_ci
  ROW_FORMAT = Dynamic
  COMMENT ='采购到货单主表';

DROP TABLE IF EXISTS `purchase_receipt_item`;
CREATE TABLE `purchase_receipt_item`
(
    id                        INT            NOT NULL AUTO_INCREMENT PRIMARY KEY,
    del_state                 INT            NOT NULL DEFAULT 0 COMMENT '0-正常 1-删除',
    del_time                  TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '删除时间',
    create_time               TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    purchase_receipt_item_id  BIGINT         NOT NULL COMMENT '分布式唯一ID',
    purchase_receipt_id       BIGINT         NOT NULL DEFAULT 0 COMMENT '到货单ID',
    receipt_no                VARCHAR(64)    NOT NULL DEFAULT '' COMMENT '到货单号',
    line_no                   INT            NOT NULL DEFAULT 1 COMMENT '行号',
    purchase_order_id         BIGINT         NOT NULL DEFAULT 0 COMMENT '采购单ID',
    purchase_order_no         VARCHAR(64)    NOT NULL DEFAULT '' COMMENT '采购单号',
    purchase_order_item_id    BIGINT         NOT NULL DEFAULT 0 COMMENT '采购明细ID',
    supplier_id               BIGINT         NOT NULL DEFAULT 0 COMMENT '供应商ID',
    supplier_name             VARCHAR(128)   NOT NULL DEFAULT '' COMMENT '供应商名称',
    material_id               BIGINT         NOT NULL DEFAULT 0 COMMENT '物料ID',
    material_code             VARCHAR(64)    NOT NULL DEFAULT '' COMMENT '物料编码',
    material_name             VARCHAR(128)   NOT NULL DEFAULT '' COMMENT '物料名称',
    material_spec             VARCHAR(128)   NOT NULL DEFAULT '' COMMENT '物料规格',
    unit_name                 VARCHAR(32)    NOT NULL DEFAULT '' COMMENT '单位',
    ordered_quantity          DECIMAL(18, 4) NOT NULL DEFAULT 0.0000 COMMENT '订单数量',
    receipt_quantity          DECIMAL(18, 4) NOT NULL DEFAULT 0.0000 COMMENT '本次到货数量',
    cumulative_quantity       DECIMAL(18, 4) NOT NULL DEFAULT 0.0000 COMMENT '累计到货数量',
    price                     DECIMAL(18, 4) NOT NULL DEFAULT 0.0000 COMMENT '不含税单价',
    tax_price                 DECIMAL(18, 4) NOT NULL DEFAULT 0.0000 COMMENT '含税单价',
    amount                    DECIMAL(18, 2) NOT NULL DEFAULT 0.00 COMMENT '金额',
    warehouse_name            VARCHAR(128)   NOT NULL DEFAULT '' COMMENT '收货仓库',
    batch_no                  VARCHAR(64)    NOT NULL DEFAULT '' COMMENT '批次号',
    remark                    VARCHAR(1024)  NOT NULL DEFAULT '' COMMENT '备注',
    UNIQUE KEY uk_purchase_receipt_item_id (`purchase_receipt_item_id`),
    UNIQUE KEY uk_purchase_receipt_line (`purchase_receipt_id`, `line_no`),
    KEY idx_purchase_order_id (`purchase_order_id`),
    KEY idx_purchase_order_item_id (`purchase_order_item_id`),
    KEY idx_material_id (`material_id`),
    KEY idx_material_code (`material_code`),
    KEY idx_material_name (`material_name`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_general_ci
  ROW_FORMAT = Dynamic
  COMMENT ='采购到货单明细表';

DROP TABLE IF EXISTS `purchase_invoice`;
CREATE TABLE `purchase_invoice`
(
    id                     INT            NOT NULL AUTO_INCREMENT PRIMARY KEY,
    del_state              INT            NOT NULL DEFAULT 0 COMMENT '0-正常 1-删除',
    del_time               TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '删除时间',
    create_time            TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    purchase_invoice_id    BIGINT         NOT NULL COMMENT '分布式唯一ID',
    invoice_no             VARCHAR(64)    NOT NULL DEFAULT '' COMMENT '发票号',
    supplier_id            BIGINT         NOT NULL DEFAULT 0 COMMENT '供应商ID',
    supplier_name          VARCHAR(128)   NOT NULL DEFAULT '' COMMENT '供应商名称',
    invoice_title          VARCHAR(255)   NOT NULL DEFAULT '' COMMENT '发票抬头',
    invoice_date           DATE           NOT NULL COMMENT '开票日期',
    audit_state            TINYINT        NOT NULL DEFAULT 0 COMMENT '审核状态 0-待审核 1-已审核 2-反审核 3-作废',
    invoice_type           TINYINT        NOT NULL DEFAULT 0 COMMENT '发票类型 0-未分类 1-专票 2-普票 3-收据',
    total_invoice_amount   DECIMAL(18, 2) NOT NULL DEFAULT 0.00 COMMENT '总开票金额',
    total_no_tax_amount    DECIMAL(18, 2) NOT NULL DEFAULT 0.00 COMMENT '总不含税金额',
    total_tax_amount       DECIMAL(18, 2) NOT NULL DEFAULT 0.00 COMMENT '总税额',
    total_discount_amount  DECIMAL(18, 2) NOT NULL DEFAULT 0.00 COMMENT '总折扣金额',
    paid_amount            DECIMAL(18, 2) NOT NULL DEFAULT 0.00 COMMENT '已付款金额',
    unpaid_amount          DECIMAL(18, 2) NOT NULL DEFAULT 0.00 COMMENT '未付款金额',
    maker_user_id          BIGINT         NOT NULL DEFAULT 0 COMMENT '开单员ID',
    maker_user_name        VARCHAR(64)    NOT NULL DEFAULT '' COMMENT '开单员',
    audit_user_id          BIGINT         NOT NULL DEFAULT 0 COMMENT '审核人ID',
    audit_user_name        VARCHAR(64)    NOT NULL DEFAULT '' COMMENT '审核人',
    audit_time             DATETIME                DEFAULT NULL COMMENT '审核时间',
    remark                 VARCHAR(1024)  NOT NULL DEFAULT '' COMMENT '备注',
    UNIQUE KEY uk_purchase_invoice_id (`purchase_invoice_id`),
    KEY idx_invoice_no (`invoice_no`),
    KEY idx_supplier_id (`supplier_id`),
    KEY idx_supplier_name (`supplier_name`),
    KEY idx_invoice_date (`invoice_date`),
    KEY idx_audit_state (`audit_state`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_general_ci
  ROW_FORMAT = Dynamic
  COMMENT ='进项发票主表';

DROP TABLE IF EXISTS `purchase_invoice_bind`;
CREATE TABLE `purchase_invoice_bind`
(
    id                        INT            NOT NULL AUTO_INCREMENT PRIMARY KEY,
    del_state                 INT            NOT NULL DEFAULT 0 COMMENT '0-正常 1-删除',
    del_time                  TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '删除时间',
    create_time               TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    purchase_invoice_bind_id  BIGINT         NOT NULL COMMENT '分布式唯一ID',
    purchase_invoice_id       BIGINT         NOT NULL DEFAULT 0 COMMENT '发票ID',
    invoice_no                VARCHAR(64)    NOT NULL DEFAULT '' COMMENT '发票号',
    purchase_order_id         BIGINT         NOT NULL DEFAULT 0 COMMENT '采购单ID',
    purchase_order_no         VARCHAR(64)    NOT NULL DEFAULT '' COMMENT '采购单号',
    supplier_id               BIGINT         NOT NULL DEFAULT 0 COMMENT '供应商ID',
    supplier_name             VARCHAR(128)   NOT NULL DEFAULT '' COMMENT '供应商名称',
    tax_rate                  DECIMAL(10, 2) NOT NULL DEFAULT 0.00 COMMENT '税率',
    invoice_amount            DECIMAL(18, 2) NOT NULL DEFAULT 0.00 COMMENT '本单绑定开票金额',
    no_tax_amount             DECIMAL(18, 2) NOT NULL DEFAULT 0.00 COMMENT '不含税金额',
    tax_amount                DECIMAL(18, 2) NOT NULL DEFAULT 0.00 COMMENT '税额',
    discount_amount           DECIMAL(18, 2) NOT NULL DEFAULT 0.00 COMMENT '折扣金额',
    paid_amount               DECIMAL(18, 2) NOT NULL DEFAULT 0.00 COMMENT '已付款金额',
    remark                    VARCHAR(1024)  NOT NULL DEFAULT '' COMMENT '备注',
    UNIQUE KEY uk_purchase_invoice_bind_id (`purchase_invoice_bind_id`),
    KEY idx_purchase_invoice_id (`purchase_invoice_id`),
    KEY idx_purchase_order_id (`purchase_order_id`),
    KEY idx_purchase_order_no (`purchase_order_no`),
    KEY idx_supplier_id (`supplier_id`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_general_ci
  ROW_FORMAT = Dynamic
  COMMENT ='进项发票与采购单绑定表';

DROP TABLE IF EXISTS `purchase_invoice_item`;
CREATE TABLE `purchase_invoice_item`
(
    id                        INT            NOT NULL AUTO_INCREMENT PRIMARY KEY,
    del_state                 INT            NOT NULL DEFAULT 0 COMMENT '0-正常 1-删除',
    del_time                  TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '删除时间',
    create_time               TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    purchase_invoice_item_id  BIGINT         NOT NULL COMMENT '分布式唯一ID',
    purchase_invoice_id       BIGINT         NOT NULL DEFAULT 0 COMMENT '发票ID',
    purchase_invoice_bind_id  BIGINT         NOT NULL DEFAULT 0 COMMENT '发票绑定ID',
    invoice_no                VARCHAR(64)    NOT NULL DEFAULT '' COMMENT '发票号',
    purchase_order_id         BIGINT         NOT NULL DEFAULT 0 COMMENT '采购单ID',
    purchase_order_no         VARCHAR(64)    NOT NULL DEFAULT '' COMMENT '采购单号',
    purchase_order_item_id    BIGINT         NOT NULL DEFAULT 0 COMMENT '采购明细ID',
    supplier_id               BIGINT         NOT NULL DEFAULT 0 COMMENT '供应商ID',
    supplier_name             VARCHAR(128)   NOT NULL DEFAULT '' COMMENT '供应商名称',
    invoice_date              DATE           NOT NULL COMMENT '开票日期',
    material_id               BIGINT         NOT NULL DEFAULT 0 COMMENT '物料ID',
    material_code             VARCHAR(64)    NOT NULL DEFAULT '' COMMENT '存货编码',
    material_name             VARCHAR(128)   NOT NULL DEFAULT '' COMMENT '物料名称',
    material_spec             VARCHAR(128)   NOT NULL DEFAULT '' COMMENT '规格型号',
    unit_name                 VARCHAR(32)    NOT NULL DEFAULT '' COMMENT '单位',
    quantity                  DECIMAL(18, 4) NOT NULL DEFAULT 0.0000 COMMENT '数量',
    price                     DECIMAL(18, 8) NOT NULL DEFAULT 0.00000000 COMMENT '含税单价',
    no_tax_price              DECIMAL(18, 8) NOT NULL DEFAULT 0.00000000 COMMENT '不含税单价',
    tax_rate                  DECIMAL(10, 2) NOT NULL DEFAULT 0.00 COMMENT '税率',
    amount                    DECIMAL(18, 2) NOT NULL DEFAULT 0.00 COMMENT '含税金额',
    no_tax_amount             DECIMAL(18, 2) NOT NULL DEFAULT 0.00 COMMENT '不含税金额',
    tax_amount                DECIMAL(18, 2) NOT NULL DEFAULT 0.00 COMMENT '税额',
    invoice_amount            DECIMAL(18, 2) NOT NULL DEFAULT 0.00 COMMENT '开票金额',
    discount_amount           DECIMAL(18, 2) NOT NULL DEFAULT 0.00 COMMENT '折扣金额',
    paid_amount               DECIMAL(18, 2) NOT NULL DEFAULT 0.00 COMMENT '已付款金额',
    remark                    VARCHAR(1024)  NOT NULL DEFAULT '' COMMENT '备注',
    UNIQUE KEY uk_purchase_invoice_item_id (`purchase_invoice_item_id`),
    KEY idx_purchase_invoice_id (`purchase_invoice_id`),
    KEY idx_purchase_order_id (`purchase_order_id`),
    KEY idx_purchase_order_item_id (`purchase_order_item_id`),
    KEY idx_material_id (`material_id`),
    KEY idx_material_code (`material_code`),
    KEY idx_material_name (`material_name`),
    KEY idx_invoice_date (`invoice_date`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_general_ci
  ROW_FORMAT = Dynamic
  COMMENT ='进项发票明细表';

DROP TABLE IF EXISTS `purchase_payment`;
CREATE TABLE `purchase_payment`
(
    id                     INT            NOT NULL AUTO_INCREMENT PRIMARY KEY,
    del_state              INT            NOT NULL DEFAULT 0 COMMENT '0-正常 1-删除',
    del_time               TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '删除时间',
    create_time            TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    purchase_payment_id    BIGINT         NOT NULL COMMENT '分布式唯一ID',
    payment_no             VARCHAR(64)    NOT NULL DEFAULT '' COMMENT '付款单号',
    supplier_id            BIGINT         NOT NULL DEFAULT 0 COMMENT '供应商ID',
    supplier_name          VARCHAR(128)   NOT NULL DEFAULT '' COMMENT '供应商名称',
    payment_date           DATE           NOT NULL COMMENT '付款日期',
    payment_method         TINYINT        NOT NULL DEFAULT 0 COMMENT '付款方式 0-其他 1-现金 2-转账 3-微信 4-支付宝 5-承兑',
    audit_state            TINYINT        NOT NULL DEFAULT 0 COMMENT '审核状态 0-待审核 1-已审核 2-反审核 3-作废',
    total_amount           DECIMAL(18, 2) NOT NULL DEFAULT 0.00 COMMENT '付款总金额',
    maker_user_id          BIGINT         NOT NULL DEFAULT 0 COMMENT '制单人ID',
    maker_user_name        VARCHAR(64)    NOT NULL DEFAULT '' COMMENT '制单人',
    audit_user_id          BIGINT         NOT NULL DEFAULT 0 COMMENT '审核人ID',
    audit_user_name        VARCHAR(64)    NOT NULL DEFAULT '' COMMENT '审核人',
    audit_time             DATETIME                DEFAULT NULL COMMENT '审核时间',
    remark                 VARCHAR(1024)  NOT NULL DEFAULT '' COMMENT '备注',
    UNIQUE KEY uk_purchase_payment_id (`purchase_payment_id`),
    UNIQUE KEY uk_payment_no (`payment_no`),
    KEY idx_supplier_id (`supplier_id`),
    KEY idx_supplier_name (`supplier_name`),
    KEY idx_payment_date (`payment_date`),
    KEY idx_audit_state (`audit_state`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_general_ci
  ROW_FORMAT = Dynamic
  COMMENT ='采购付款单主表';

DROP TABLE IF EXISTS `purchase_payment_bind`;
CREATE TABLE `purchase_payment_bind`
(
    id                        INT            NOT NULL AUTO_INCREMENT PRIMARY KEY,
    del_state                 INT            NOT NULL DEFAULT 0 COMMENT '0-正常 1-删除',
    del_time                  TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '删除时间',
    create_time               TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    purchase_payment_bind_id  BIGINT         NOT NULL COMMENT '分布式唯一ID',
    purchase_payment_id       BIGINT         NOT NULL DEFAULT 0 COMMENT '付款单ID',
    payment_no                VARCHAR(64)    NOT NULL DEFAULT '' COMMENT '付款单号',
    purchase_invoice_id       BIGINT         NOT NULL DEFAULT 0 COMMENT '发票ID',
    invoice_no                VARCHAR(64)    NOT NULL DEFAULT '' COMMENT '发票号',
    purchase_order_id         BIGINT         NOT NULL DEFAULT 0 COMMENT '采购单ID',
    purchase_order_no         VARCHAR(64)    NOT NULL DEFAULT '' COMMENT '采购单号',
    supplier_id               BIGINT         NOT NULL DEFAULT 0 COMMENT '供应商ID',
    supplier_name             VARCHAR(128)   NOT NULL DEFAULT '' COMMENT '供应商名称',
    pay_amount                DECIMAL(18, 2) NOT NULL DEFAULT 0.00 COMMENT '付款金额',
    remark                    VARCHAR(1024)  NOT NULL DEFAULT '' COMMENT '备注',
    UNIQUE KEY uk_purchase_payment_bind_id (`purchase_payment_bind_id`),
    KEY idx_purchase_payment_id (`purchase_payment_id`),
    KEY idx_purchase_invoice_id (`purchase_invoice_id`),
    KEY idx_purchase_order_id (`purchase_order_id`),
    KEY idx_supplier_id (`supplier_id`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_general_ci
  ROW_FORMAT = Dynamic
  COMMENT ='采购付款绑定表';
