<?php

namespace Yonna\Services\Log\Sql;

class Sql
{
    const mysql = "CREATE TABLE `y_log`(
                        `id` bigint NOT NULL AUTO_INCREMENT COMMENT 'id',
                        `key` char(255) NOT NULL DEFAULT 'default' COMMENT 'key',
                        `type` char(255) NOT NULL DEFAULT 'info' COMMENT '类型',
                        `record_timestamp` int NOT NULL COMMENT '时间戳',
                        `data` json COMMENT 'data',
                        PRIMARY KEY (`id`)
                    ) ENGINE = INNODB COMMENT 'yonna log';";


    const pgsql = "CREATE TABLE `y_log`(
                        `id` bigserial NOT NULL,
                        `key` text NOT NULL DEFAULT 'default',
                        `type` text NOT NULL DEFAULT 'info',
                        `record_timestamp` integer unsigned NOT NULL,
                        `data` jsonb,
                        PRIMARY KEY (`id`)
                    ) ENGINE = INNODB COMMENT 'yonna log';";
}