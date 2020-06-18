<?php

namespace Yonna\QuickStart\Sql;

class User
{
    const mysql = [

        "create table `license` (
            `id` bigint unsigned auto_increment not null comment '许可id',
            `name` char(255) not null default '' comment '许可名字',
            `api` varchar(2048) not null default '' comment '许可支持的api',
            `extends_id` bigint unsigned not null comment '许可继承',
            primary key (`id`),
            unique key (`name`),
            index (`extends_id`)
        ) engine = innodb comment '许可关系';",

        "create table `user`(
            `uid` bigint unsigned auto_increment not null comment '用户uid',
            `status` tinyint not null default -1 comment '状态 -5冻结 -2未通过 -1未审核 1正常',
            `password` char(255) not null default '' comment '登录密码，不一定有，如通过微信登录的就没有',
            `source` char(255) not null default 'unknow' comment '来源',
            `inviter_uid` bigint not null default 0 comment '邀请人uid',
            `register_time` datetime not null default '1970-01-01 00:00:00' comment '注册时间',
            primary key (`uid`),
            index (`status`)
        ) engine = innodb comment '用户表';",

        "create table `user_account`(
            `uid` bigint unsigned not null comment '用户uid',
            `type` char(255) not null default '' comment '账号类型：name|phone|email|wx_open_id|wx_union_id',
            `string` char(255) not null default '' comment '账号字串值',
            `allow_login` tinyint not null default -1 comment '是否允许登录'
        ) engine = innodb comment '用户账号拓展表';",

        "create table `user_license` (
            `uid` bigint unsigned not null comment 'uid',
            `license_id` char(255) not null default '' comment '许可id',
            `start_time` datetime not null default '1970-01-01 00:00:00' comment '起效时间',
            `end_time` datetime not null default '1970-01-01 00:00:00' comment '过期时间',
            index (`uid`)
        ) engine = innodb comment '用户获得许可证';",

        "create table `user_identity`(
            `uid` bigint unsigned not null comment '用户uid',
            `name` char(255) not null default '' comment '身份证姓名（真实姓名）',
            `card_no` char(255) not null default '' comment '身份证号',
            `card_pic_front` json comment '身份证正面',
            `card_pic_back` json comment '身份证背面',
            `card_pic_take` json comment '身份证手持',
            `card_expire_date` date not null default '1970-01-01' comment '身份证过期日期',
            `auth_status` smallint not null default -1 comment '实名认证状态 -1未认证 -2未通过 1认证中 10已认证',
            `auth_reject_reason` varchar(1024) not null default '' comment '实名认证拒绝理由',
            primary key (`uid`)
        ) engine = innodb comment '用户身份证拓展表';",

        "create table `user_meta` (
            `key` char(255) not null default '' comment 'meta key',
            `type` char(255) not null default '' comment '类型',
            `ordering` int not null default 0 comment '排序,升序',
            primary key (`key`),
            index (`type`)
        ) engine = innodb comment '用户可变自定义字段';",

        "create table `user_meta_info` (
            `uid` bigint unsigned not null default 0 comment '用户uid',
            `mete_key` char(255) not null default '' comment 'meta key',
            `mete_value` varchar(2048) not null default '' comment 'meta value',
            `ordering` int not null default 0 comment '排序,升序',
            primary key (`uid`),
            index (`mete_key`)
        ) engine = innodb comment '用户可变自定义信息';"

    ];


}