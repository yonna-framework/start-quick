CREATE TABLE `y_i18n`
(
    `unique_key` char(255) NOT NULL DEFAULT '' COMMENT '验证key',
    `zh_cn`      char(255) NOT NULL DEFAULT '' COMMENT '简体中文',
    `zh_hk`      char(255) NOT NULL DEFAULT '' COMMENT '香港繁体',
    `zh_tw`      char(255) NOT NULL DEFAULT '' COMMENT '台湾繁体',
    `en_us`      char(255) NOT NULL DEFAULT '' COMMENT '美国英语',
    `ja_jp`      char(255) NOT NULL DEFAULT '' COMMENT '日本语',
    `ko_kr`      char(255) NOT NULL DEFAULT '' COMMENT '韩国语',
    PRIMARY KEY (`unique_key`)
) ENGINE = INNODB COMMENT 'yonna i18n';

CREATE TABLE `y_log`
(
    `id`               bigint    NOT NULL AUTO_INCREMENT COMMENT 'id',
    `key`              char(255) NOT NULL DEFAULT 'default' COMMENT 'key',
    `type`             char(255) NOT NULL DEFAULT 'info' COMMENT '类型',
    `record_timestamp` int       NOT NULL COMMENT '当记录时间戳',
    `data`             json COMMENT 'data',
    PRIMARY KEY (`id`)
) ENGINE = INNODB COMMENT 'yonna log';