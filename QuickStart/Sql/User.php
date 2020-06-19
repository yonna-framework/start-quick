<?php

namespace Yonna\QuickStart\Sql;

class User
{
    const mysql = [

        "create table `y_license` (
            `id` bigint unsigned auto_increment not null comment '许可id',
            `upper_id` bigint unsigned not null default 0 comment 'y_license_id',
            `name` char(255) not null default '' comment '许可名字',
            `api` varchar(2048) not null default '' comment '许可支持的api',
            primary key (`id`),
            unique key (`name`),
            index (`upper_id`)
        ) engine = innodb comment '用户许可关系';",

        "create table `y_user`(
            `id` bigint unsigned auto_increment not null comment '用户id',
            `status` tinyint not null default -1 comment '状态[-3冻结,-2审核驳回,1待审核,2审核通过]',
            `password` char(255) not null default '' comment '登录密码，不一定有，如通过微信登录的就没有',
            `inviter_user_id` bigint not null default 0 comment '邀请用户id[y_user_id]',
            `register_datetime` datetime not null default '1970-01-01 00:00:00' comment '注册时间',
            primary key (`user_id`),
            index (`status`)
        ) engine = innodb comment '用户核心数据';",

        "create table `y_user_account`(
            `user_id` bigint unsigned not null comment 'y_user_id',
            `type` char(255) not null default '' comment '账号类型[name|phone|email|wx_open_id|wx_union_id]',
            `string` char(255) not null default '' comment '账号字串值',
            `allow_login` tinyint not null default -1 comment '是否允许登录'
        ) engine = innodb comment '用户账号数据';",

        "create table `y_user_license` (
            `user_id` bigint unsigned not null comment 'user_id',
            `license_id` char(255) not null default '' comment 'y_license_id',
            `start_datetime` datetime not null default '1970-01-01 00:00:00' comment '起效时间',
            `end_datetime` datetime not null default '1970-01-01 00:00:00' comment '过期时间',
            index (`user_id`)
        ) engine = innodb comment '用户许可证数据';",

        "create table `y_user_identity`(
            `user_id` bigint unsigned not null comment '用户user_id',
            `name` char(255) not null default '' comment '身份证姓名（真实姓名）',
            `card_no` char(255) not null default '' comment '身份证号',
            `card_pic_front` json comment '身份证正面',
            `card_pic_back` json comment '身份证背面',
            `card_pic_take` json comment '身份证手持',
            `card_expire_date` date not null default '1970-01-01' comment '身份证过期日期',
            `auth_status` tinyint not null default -1 comment '实名认证状态[-1未认证,-2未通过,1认证中,3已认证]',
            `auth_reject_reason` varchar(1024) not null default '' comment '实名认证拒绝理由',
            primary key (`user_id`)
        ) engine = innodb comment '用户身份证拓展';",

        "create table `y_user_meta` (
            `key` char(255) not null default '' comment 'meta key',
            `type` char(255) not null default '' comment '类型',
            `ordering` int not null default 0 comment '排序,升序',
            primary key (`key`),
            index (`type`)
        ) engine = innodb comment '用户可变自定义字段';",

        "create table `user_meta_info` (
            `user_id` bigint unsigned not null default 0 comment '用户user_id',
            `mete_key` char(255) not null default '' comment 'meta key',
            `mete_value` varchar(2048) not null default '' comment 'meta value',
            `ordering` int not null default 0 comment '排序,升序',
            primary key (`user_id`),
            index (`mete_key`)
        ) engine = innodb comment '用户可变自定义信息';"

    ];


}