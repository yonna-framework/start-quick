<?php

namespace Yonna\QuickStart\Sql;

class I18n
{
    const mysql = "CREATE TABLE IF NOT EXISTS `%s`(
                        `unique_key` char(255) NOT NULL DEFAULT '' COMMENT '验证key',
                        `zh_cn`      char(255) NOT NULL DEFAULT '' COMMENT '简体中文',
                        `zh_hk`      char(255) NOT NULL DEFAULT '' COMMENT '香港繁体',
                        `zh_tw`      char(255) NOT NULL DEFAULT '' COMMENT '台湾繁体',
                        `en_us`      char(255) NOT NULL DEFAULT '' COMMENT '美国英语',
                        `ja_jp`      char(255) NOT NULL DEFAULT '' COMMENT '日本语',
                        `ko_kr`      char(255) NOT NULL DEFAULT '' COMMENT '韩国语',
                        PRIMARY KEY (`unique_key`)
                    ) ENGINE = INNODB COMMENT 'i18n by yonna';";


    const pgsql = "CREATE TABLE IF NOT EXISTS `%s`(
                        `unique_key` text NOT NULL DEFAULT '',
                        `zh_cn`      text NOT NULL DEFAULT '',
                        `zh_hk`      text NOT NULL DEFAULT '',
                        `zh_tw`      text NOT NULL DEFAULT '',
                        `en_us`      text NOT NULL DEFAULT '',
                        `ja_jp`      text NOT NULL DEFAULT '',
                        `ko_kr`      text NOT NULL DEFAULT '',
                        PRIMARY KEY (`unique_key`)
                    ) ENGINE = INNODB COMMENT 'i18n by yonna';";
}