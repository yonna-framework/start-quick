<?php

namespace Yonna\QuickStart\Sql;

class Essay
{
    const mysql = [

        "create table `y_essay_category` (
            `id` bigint unsigned auto_increment not null comment 'id',
            `upper_id` bigint unsigned not null default 0 comment 'y_essay_category_id',
            `name` char(255) not null default '' comment '分类名称',
            `level` bigint unsigned not null default 1 comment '分类等级',
            `description` varchar(1024) not null default '' comment '描述',
            `logo` json comment '分类logo图',
            `status` tinyint not null default 1 comment '状态[-1审核驳回,1待审核,2审核通过]',
            `ordering` int not null default 0 comment '排序[降序]',
            primary key (`id`),
            unique key (`upper_id`, `name`),
            index (`level`),
            index (`status`)
        ) engine = innodb comment '文章分类';",

        "create table `y_essay_type` (
            `id` bigint unsigned auto_increment not null comment 'id',
            `name` char(255) not null default '' comment '类型名称',
            `description` varchar(1024) not null default '' comment '描述',
            `logo` json comment '分类logo图',
            `status` tinyint not null default 1 comment '状态[-1审核驳回,1待审核,2审核通过]',
            `ordering` int not null default 0 comment '排序[降序]',
            primary key (`id`),
            unique key (`name`),
            index (`status`)
        ) engine = innodb comment '文章类型';",

        "create table `y_essay` (
            `id` bigint unsigned auto_increment not null comment 'id',
            `uid` bigint unsigned not null default 0 comment 'y_user_uid',
            `type_id` bigint unsigned not null default 0 comment 'y_essay_type_id',
            `category_id` bigint unsigned not null default 0 comment 'y_essay_category_id',
            `status` tinyint not null default 1 comment '状态[-1审核驳回,1待审核,2审核通过]',
            `views` bigint unsigned not null default 0 comment '浏览量',
            `data` json comment '文章数据',
            `ordering` int not null default 0 comment '排序[降序]',
            primary key (`id`),
            index (`type`,`type_id`,`category_id`,`status`)
        ) engine = innodb comment '文章';",

    ];


}