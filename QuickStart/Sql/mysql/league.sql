CREATE TABLE `data_hobby`
(
    `id`   bigint unsigned auto_increment not null comment 'id',
    `name` char(255)                      not null comment '名称'
) ENGINE = INNODB COMMENT '兴趣爱好';

CREATE TABLE `data_work`
(
    `id`   bigint unsigned auto_increment not null comment 'id',
    `name` char(255)                      not null comment '名称'
) ENGINE = INNODB COMMENT '职业工作';

CREATE TABLE `data_speciality`
(
    `id`   bigint unsigned auto_increment not null comment 'id',
    `name` char(255)                      not null comment '名称'
) ENGINE = INNODB COMMENT '特长';

CREATE TABLE `league`
(
    `id`                   bigint unsigned auto_increment not null comment 'id',
    `master_user_id`       bigint unsigned                not null comment '社团拥有者user_id',
    `name`                 char(255)                      not null comment '社团名',
    `business_license_pic` char(255)                      not null comment '营业执照',
    `status`               tinyint                        not null default 1 comment '状态[-2作废,-1申请驳回,1待审核,2审核通过]',
    `apply_reason`         char(255)                      not null default '' comment '申请理由',
    `rejection_reason`     char(255)                      not null default '' comment '驳回理由',
    `passed_reason`        char(255)                      not null default '' comment '通过理由',
    `delete_reason`        char(255)                      not null default '' comment '作废理由',
    `apply_datetime`       datetime                                default '1970-01-01 00:00:00' not null comment '申请日期时间',
    `rejection_datetime`   datetime                                default '1970-01-01 00:00:00' not null comment '驳回日期时间',
    `pass_datetime`        datetime                                default '1970-01-01 00:00:00' not null comment '通过日期时间',
    `delete_datetime`      datetime                                default '1970-01-01 00:00:00' not null comment '作废日期时间',
    PRIMARY KEY (`id`),
    INDEX (`master_user_id`),
    INDEX (`name`)
) ENGINE = INNODB COMMENT '社团';

CREATE TABLE `league_associate_administrator`
(
    `league_id`          bigint unsigned not null comment '社团id',
    `user_id`            bigint unsigned not null comment '管理员user_id',
    `status`             tinyint         not null default 1 comment '状态[-2作废,-1申请驳回,1待审核,2审核通过]',
    `apply_reason`       char(255)       not null default '' comment '申请理由',
    `rejection_reason`   char(255)       not null default '' comment '驳回理由',
    `passed_reason`      char(255)       not null default '' comment '通过理由',
    `delete_reason`      char(255)       not null default '' comment '作废理由',
    `apply_datetime`     datetime                 default '1970-01-01 00:00:00' not null comment '申请日期时间',
    `rejection_datetime` datetime                 default '1970-01-01 00:00:00' not null comment '驳回日期时间',
    `pass_datetime`      datetime                 default '1970-01-01 00:00:00' not null comment '通过日期时间',
    `delete_datetime`    datetime                 default '1970-01-01 00:00:00' not null comment '作废日期时间',
    INDEX (`league_id`),
    INDEX (`user_id`)
) ENGINE = INNODB COMMENT '社团与管理员用户关系';

CREATE TABLE `league_associate_hobby`
(
    `league_id` bigint unsigned not null comment '社团id',
    `data_id`   bigint unsigned not null comment '爱好id',
    INDEX (`league_id`),
    INDEX (`data_id`)
) ENGINE = INNODB COMMENT '社团与爱好关系';

CREATE TABLE `league_associate_work`
(
    `league_id` bigint unsigned not null comment '社团id',
    `data_id`   bigint unsigned not null comment '职业工作id',
    INDEX (`league_id`),
    INDEX (`data_id`)
) ENGINE = INNODB COMMENT '社团与职业工作关系';

CREATE TABLE `league_associate_speciality`
(
    `league_id` bigint unsigned not null comment '社团id',
    `data_id`   bigint unsigned not null comment '特长id',
    INDEX (`league_id`),
    INDEX (`data_id`)
) ENGINE = INNODB COMMENT '社团与特长关系';

CREATE TABLE `league_mission`
(
    `id`                  bigint unsigned auto_increment not null comment 'id',
    `league_id`           bigint unsigned                not null comment '发起社团league_id',
    `user_id`             bigint unsigned                not null comment '发起人user_id',
    `name`                char(255)                      not null comment '任务名称',
    `introduction`        varchar(1024)                  not null comment '任务介绍',
    `age_range`           varchar(1024)                  not null comment '范围:年龄',
    `hobby_range`         varchar(1024)                  not null comment '范围:爱好',
    `work_range`          varchar(1024)                  not null comment '范围:工作',
    `speciality_range`    varchar(1024)                  not null comment '范围:特长',
    `points`              numeric(7, 1)                  not null default 0.0 comment '任务价值分数',
    `status`              tinyint                        not null default 1 comment '状态[-2作废,-1申请驳回,1待审核,2审核通过]',
    `apply_reason`        char(255)                      not null default '' comment '申请理由',
    `rejection_reason`    char(255)                      not null default '' comment '驳回理由',
    `passed_reason`       char(255)                      not null default '' comment '通过理由',
    `delete_reason`       char(255)                      not null default '' comment '作废理由',
    `apply_datetime`      datetime                       not null default '1970-01-01 00:00:00' not null comment '申请日期时间',
    `rejection_datetime`  datetime                       not null default '1970-01-01 00:00:00' not null comment '驳回日期时间',
    `pass_datetime`       datetime                       not null default '1970-01-01 00:00:00' not null comment '通过日期时间',
    `delete_datetime`     datetime                       not null default '1970-01-01 00:00:00' not null comment '作废日期时间',
    `self_evaluation`     numeric(3, 1)                  not null default 0.0 comment '自我评分',
    `platform_evaluation` numeric(3, 1)                  not null default 0.0 comment '平台评分',
    PRIMARY KEY (`id`),
    INDEX (`user_id`),
    INDEX (`name`)
) ENGINE = INNODB COMMENT '社团任务';

CREATE TABLE `league_mission_joiner`
(
    `mission_id`          bigint unsigned not null comment '参与的任务user_id',
    `user_id`             bigint unsigned not null comment '参与人user_id',
    `league_id`           bigint unsigned not null comment '参与人所在社团league_id',
    `status`              tinyint         not null default 1 comment '状态[-2作废,-1申请驳回,1待审核,2审核通过]',
    `apply_reason`        char(255)       not null default '' comment '申请理由',
    `rejection_reason`    char(255)       not null default '' comment '驳回理由',
    `passed_reason`       char(255)       not null default '' comment '通过理由',
    `delete_reason`       char(255)       not null default '' comment '作废理由',
    `apply_datetime`      datetime        not null default '1970-01-01 00:00:00' not null comment '申请日期时间',
    `rejection_datetime`  datetime        not null default '1970-01-01 00:00:00' not null comment '驳回日期时间',
    `pass_datetime`       datetime        not null default '1970-01-01 00:00:00' not null comment '通过日期时间',
    `delete_datetime`     datetime        not null default '1970-01-01 00:00:00' not null comment '作废日期时间',
    `self_evaluation`     numeric(3, 1)   not null default 0.0 comment '自我评分',
    `platform_evaluation` numeric(3, 1)   not null default 0.0 comment '平台评分',
    PRIMARY KEY (`mission_id`, `user_id`),
    INDEX (`user_id`)
) ENGINE = INNODB COMMENT '社团任务参加者';
