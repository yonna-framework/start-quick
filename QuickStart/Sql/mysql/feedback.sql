create table `feedback`
(
    `id`                bigint unsigned auto_increment not null comment 'id',
    `user_id`           bigint unsigned                not null default 0 comment 'user_id',
    `content`           varchar(1024)                  not null default '' comment '问题内容',
    `answer`            varchar(1024)                  not null default '' comment '答复内容',
    `ip`                char(255)                      not null default '' comment 'ip地址',
    `website_url`       char(255)                      not null default '' comment '反馈地址',
    `contact_name`      char(255)                      not null default '' comment '联系人',
    `contact_phone`     char(255)                      not null default '' comment '联系电话',
    `remarks`           varchar(1024)                  not null default '' comment '处理备注',
    `feedback_datetime` datetime                       not null default '1970-01-01 00:00:00' comment '反馈时间',
    primary key (`id`)
) engine = innodb comment '反馈';