CREATE
DATABASE jykj_hgj
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `admin_user`;
CREATE TABLE `admin_user`
(
    id               INT           NOT NULL AUTO_INCREMENT PRIMARY KEY,
    del_state        INT           NOT NULL DEFAULT 0 COMMENT '0-正常 1-删除',
    del_time         TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '删除时间',
    create_time      TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',

    admin_user_id    BIGINT        NOT NULL DEFAULT 0 COMMENT '分布式唯一ID',
    username         VARCHAR(64)   NOT NULL DEFAULT '' COMMENT '登录账号',
    phone_number     VARCHAR(20)   NOT NULL DEFAULT '' COMMENT '手机号',
    password         VARCHAR(128)  NOT NULL DEFAULT '' COMMENT '密码hash',
    salt             VARCHAR(32)   NOT NULL DEFAULT '' COMMENT '密码盐值',
    nickname         VARCHAR(64)   NOT NULL DEFAULT '' COMMENT '管理员昵称',
    avatar           VARCHAR(512)  NOT NULL DEFAULT '' COMMENT '头像url',

    real_name        VARCHAR(64)   NOT NULL DEFAULT '' COMMENT '真实姓名',
    gender           TINYINT       NOT NULL DEFAULT 0 COMMENT '0-未知 1-男 2-女',
    email            VARCHAR(128)  NOT NULL DEFAULT '' COMMENT '邮箱',
    department_name  VARCHAR(64)   NOT NULL DEFAULT '' COMMENT '所属部门',
    role_name        VARCHAR(64)   NOT NULL DEFAULT '' COMMENT '角色名称',

    admin_state      TINYINT       NOT NULL DEFAULT 1 COMMENT '1-启用 2-禁用',
    is_super_admin   TINYINT       NOT NULL DEFAULT 0 COMMENT '0-否 1-是',
    login_error_count INT          NOT NULL DEFAULT 0 COMMENT '连续登录失败次数',

    last_login_time  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '最后登录时间',
    last_login_ip    VARCHAR(64)   NOT NULL DEFAULT '' COMMENT '最后登录IP',

    remark           VARCHAR(255)  NOT NULL DEFAULT '' COMMENT '备注',

    UNIQUE KEY uk_username (`username`),
    UNIQUE KEY uk_phone_number (`phone_number`),
    UNIQUE KEY uk_admin_user_id (`admin_user_id`),
    KEY idx_admin_state (`admin_state`)
) ENGINE = InnoDB
CHARACTER SET = utf8mb4
COLLATE = utf8mb4_general_ci
ROW_FORMAT = Dynamic;