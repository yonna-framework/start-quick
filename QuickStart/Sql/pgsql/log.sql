CREATE TABLE y_log
(
    id               bigserial PRIMARY KEY NOT NULL,
    key              text                  NOT NULL DEFAULT 'default',
    type             text                  NOT NULL DEFAULT 'info',
    record_timestamp integer               NOT NULL CHECK ( y_log.record_timestamp >= 0 ),
    data             jsonb
);
comment on table y_log is 'yonna log';
comment on column y_log.id is 'id';
comment on column y_log.key is 'key';
comment on column y_log.type is '类型';
comment on column y_log.record_timestamp is '当记录时间戳';
comment on column y_log.data is 'data';
