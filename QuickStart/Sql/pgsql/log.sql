CREATE TABLE log
(
    id               bigserial PRIMARY KEY NOT NULL,
    key              text                  NOT NULL DEFAULT 'default',
    type             text                  NOT NULL DEFAULT 'info',
    record_timestamp integer               NOT NULL CHECK ( log.record_timestamp >= 0 ),
    data             jsonb
);
comment on table log is 'yonna log';
comment on column log.id is 'id';
comment on column log.key is 'key';
comment on column log.type is '类型';
comment on column log.record_timestamp is '当记录时间戳';
comment on column log.data is 'data';
