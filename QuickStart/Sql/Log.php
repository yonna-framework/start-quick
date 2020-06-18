<?php

namespace Yonna\QuickStart\Sql;

class Log
{
    const mysql = "CREATE TABLE IF NOT EXISTS `%s`(
                        `id` bigint NOT NULL AUTO_INCREMENT COMMENT 'id',
                        `key` char(255) NOT NULL DEFAULT 'default' COMMENT 'key',
                        `type` char(255) NOT NULL DEFAULT 'info' COMMENT '类型',
                        `log_time` int NOT NULL COMMENT '时间戳',
                        `data` json COMMENT 'data',
                        PRIMARY KEY (`id`)
                    ) ENGINE = INNODB COMMENT 'log by yonna';";


    const pgsql = "CREATE TABLE IF NOT EXISTS `%s`(
                        `id` bigserial NOT NULL,
                        `key` text NOT NULL DEFAULT 'default',
                        `type` text NOT NULL DEFAULT 'info',
                        `log_time` integer NOT NULL,
                        `data` jsonb,
                        PRIMARY KEY (`id`)
                    ) ENGINE = INNODB COMMENT 'log by yonna';";
}