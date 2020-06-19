<?php

namespace Yonna\QuickStart\Sql;

class Sdk
{
    const mysql = [

        "create table `y_sdk_config` (
            `user_id` bigint unsigned not null default 0 comment 'y_user_id',
            `data` json comment '数据',
            primary key (`user_id`)
        ) engine = innodb comment '第三方配置表';",

    ];


}