CREATE TABLE `xoss`
(
    `hash`         CHAR(255)                 NOT NULL COMMENT 'hash（这个哈希由二进制数据生成，代表文件唯一）',
    `key`          CHAR(255)                 NOT NULL COMMENT '文件key（这个key是生成的，用于访问）',
    `user_token`   CHAR(255)                 NOT NULL COMMENT '用户token',
    `name`         CHAR(255)                 NOT NULL COMMENT '文件名',
    `md5_name`     CHAR(255)                 NOT NULL COMMENT 'md5名字（这是sha1生成的md5名字）',
    `suffix`       CHAR(255)                 NOT NULL COMMENT '文件后缀',
    `size`         BIGINT UNSIGNED DEFAULT 0 NOT NULL COMMENT '文件大小',
    `content_type` CHAR(255)                 NOT NULL COMMENT '内容类型',
    `path`         CHAR(255)                 NOT NULL COMMENT '保存路径',
    `uri`          CHAR(255)                 NOT NULL COMMENT 'URI',
    `internet_url` CHAR(255)                 NOT NULL COMMENT '互联网来源网址',
    `views`        INT UNSIGNED    DEFAULT 0 NOT NULL COMMENT '热度',
    PRIMARY KEY (`hash`),
    INDEX (`key`),
    INDEX (`user_token`),
    INDEX (`name`),
    INDEX (`content_type`)
) ENGINE = INNODB COMMENT 'X-oss文件系统';