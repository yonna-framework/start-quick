create table `user`
(
    `id`                bigint unsigned auto_increment not null comment '用户id',
    `status`            tinyint                        not null default 1 comment '状态[-10注销,-3冻结,-2审核驳回,1待审核,2审核通过]',
    `password`          char(255)                      not null default '' comment '登录密码，不一定有，如通过微信登录的就没有',
    `inviter_user_id`   bigint                         not null default 0 comment '邀请用户id[y_user_id]',
    `register_datetime` datetime                       not null default '1970-01-01 00:00:00' comment '注册时间',
    primary key (`id`),
    index (`status`)
) engine = innodb comment '用户核心数据';

create table `user_account`
(
    `user_id`     bigint unsigned not null comment 'user_id',
    `type`        char(255)       not null default '' comment '账号类型[name|phone|email|wx_open_id|wx_union_id]',
    `string`      char(255)       not null default '' comment '账号字串值',
    `allow_login` tinyint         not null default -1 comment '是否允许登录'
) engine = innodb comment '用户账号数据';

create table `user_meta_category`
(
    `key`              char(255) not null default '' comment 'meta key',
    `value_format`     char(255) not null default 'string' comment '数据格式化类型',
    `value_default`    char(255) not null default '' comment '默认值',
    `component_type`   char(255) not null default 'input_string' comment '前端组件类型',
    `component_values` char(255) not null default '' comment '前端组件数据需求',
    `status`           tinyint   not null default -1 comment '状态[-1无效,1有效]',
    `ordering`         int       not null default 0 comment '排序[降序]',
    primary key (`key`),
    unique key `uk_key` (`key`),
    index (`value_format`),
    index (`value_default`),
    index (`status`)
) engine = innodb comment '用户可变自定义字段';

create table `user_meta`
(
    `user_id` bigint unsigned not null default 0 comment 'user_id',
    `key`     char(255)       not null default '' comment 'meta key',
    `value`   varchar(1024)   not null default '' comment 'meta value',
    primary key (`user_id`),
    unique key `uk_user_key` (`user_id`, `key`),
    index (`key`)
) engine = innodb comment '用户可变自定义详细信息';

create table `license`
(
    `id`          bigint unsigned auto_increment not null comment '许可id',
    `upper_id`    bigint unsigned                not null default 0 comment 'license_id',
    `name`        char(255)                      not null default '' comment '许可名字',
    `allow_scope` varchar(2048)                  not null default '' comment '许可支持的allow_scope',
    primary key (`id`),
    unique key (`name`),
    index (`upper_id`)
) engine = innodb comment '用户许可关系';

create table `user_license`
(
    `user_id`        bigint unsigned not null comment 'user_id',
    `license_id`     char(255)       not null default '' comment 'license_id',
    `start_datetime` datetime        not null default '1970-01-01 00:00:00' comment '起效时间',
    `end_datetime`   datetime        not null default '1970-01-01 00:00:00' comment '过期时间',
    index (`user_id`)
) engine = innodb comment '用户许可证数据';


insert into `license` (`name`, `upper_id`, `allow_scope`)
values ('超级管理员', 0, ',,,all');

insert into `user` (`password`, `status`, `register_datetime`)
values ('faa9a6ddddf57436961bf2d2bf4338df', 2, now());

insert into `user_license` (`user_id`, `license_id`, `start_datetime`, `end_datetime`)
values (1, 1, now(), '2099-12-31 23:59:59');

insert into `user_account` (`user_id`, `type`, `string`, `allow_login`)
values (1, 'name', 'admin', 1);

insert into `user_meta_category` (`key`, `value_format`, `status`)
values ('name', 'string', 1);
insert into `user_meta_category` (`key`, `value_format`, `status`)
values ('nickname', 'string', 1);
insert into `user_meta_category` (`key`, `value_format`, `value_default`, `status`, `component_type`,
                                  `component_values`)
values ('sex', 'integer', '-1', 1, 'select', ',,,-1,1,2');