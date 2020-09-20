create table `sdk`
(
    `key`   char(255) not null comment 'key',
    `value` char(255) not null comment '配置值',
    primary key (`key`)
) engine = innodb comment 'SDK配置表';

insert into `sdk`
values ('baidu_appid', ''),
       ('baidu_secret', ''),
       ('wxmp_appid', ''),
       ('wxmp_secret', '');

