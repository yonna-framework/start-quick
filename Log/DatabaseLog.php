<?php

namespace Yonna\Log;


use Exception;
use Throwable;
use Yonna\Database\DB;
use Yonna\Database\Driver\Mongo;
use Yonna\Database\Driver\Mysql;
use Yonna\Database\Driver\Pgsql;

class DatabaseLog
{

    private $store = 'yonna_log';
    private $config = null;

    /**
     * check yonna/database
     * DatabaseLog constructor.
     */
    public function __construct()
    {
        if (!class_exists(DB::class)) {
            trigger_error('If you want to use database log,install composer package yonna/database please.');
            return;
        }
        if (Config::getDatabase() === null) {
            trigger_error('Set Database for DatabaseLog.');
            return;
        }
        $this->config = Config::getDatabase();
    }

    /**
     * 清除日志
     */
    private function clear()
    {
        if (Config::getFileExpireDay() <= 0) {
            return;
        }
    }

    /**
     * 分页获得数据
     * @param array $options
     * @return array
     */
    public function page($options = [])
    {
        $current = $options['current'] ?? 1;
        $per = $options['per'] ?? 10;
        $res = [];
        try {
            $db = DB::connect($this->config);
            if ($db instanceof Mongo) {
                $obj = $db->collection("{$this->store}");
            } elseif ($db instanceof Mysql) {
                $obj = $db->table($this->store);
            } elseif ($db instanceof Pgsql) {
                $obj = $db->schemas('public')->table($this->store);
            } else {
                throw new Exception('Set Database for Support Driver.');
            }
            $obj = $obj->orderBy('log_time', 'desc');
            if (!empty($options['key'])) {
                $obj = $obj->equalTo('key', $options['key']);
            }
            if (!empty($options['type'])) {
                $obj = $obj->equalTo('type', $options['key']);
            }
            if (!empty($options['log_time'])) {
                $obj = $obj->between('log_time', $options['log_time']);
            }
            $res = $obj->page($current, $per);
        } catch (Throwable $e) {
            Log::file()->throwable($e, 'log_db');
        }
        return $res;
    }

    /**
     * 写入日志
     * @param $type
     * @param array $data
     * @param string $key
     */
    private function append($type, $key, array $data = [])
    {
        if (empty($key) && empty($data)) {
            return;
        }
        $db = DB::connect($this->config);
        $logData = [
            'key' => $key,
            'type' => $type,
            'log_time' => time(),
            'data' => $data,
        ];
        try {
            if ($db instanceof Mongo) {
                $db->collection($this->store)->insert($logData);
            } elseif ($db instanceof Mysql) {
                $db->query("CREATE TABLE IF NOT EXISTS `{$this->store}`(
                        `id` bigint NOT NULL AUTO_INCREMENT COMMENT 'id',
                        `key` char(255) NOT NULL DEFAULT 'default' COMMENT 'key',
                        `type` char(255) NOT NULL DEFAULT 'info' COMMENT '类型',
                        `log_time` int NOT NULL COMMENT '时间戳',
                        `data` json COMMENT 'data',
                        PRIMARY KEY (`id`)
                    ) ENGINE = INNODB COMMENT 'log by yonna';");
                $db->table($this->store)->insert($logData);
            } elseif ($db instanceof Pgsql) {
                $db->query("CREATE TABLE IF NOT EXISTS `{$this->store}`(
                        `id` bigserial NOT NULL,
                        `key` text NOT NULL DEFAULT 'default',
                        `type` text NOT NULL DEFAULT 'info',
                        `log_time` integer NOT NULL,
                        `data` jsonb,
                        PRIMARY KEY (`id`)
                    ) ENGINE = INNODB COMMENT 'log by yonna';");
                $db->schemas('public')->table($this->store)->insert($logData);
            } else {
                throw new Exception('Set Database for Support Driver.');
            }
        } catch (Throwable $e) {
            Log::file()->throwable($e);
        }

        $this->clear();
    }

    /**
     * @param string $key
     * @param Throwable $t
     */
    public function throwable(Throwable $t, $key = 'default')
    {
        $this->append(Type::THROWABLE, $key, [
            'code' => $t->getCode(),
            'message' => $t->getMessage(),
            'file' => $t->getFile(),
            'line' => $t->getLine(),
            'trace' => $t->getTrace(),
        ]);
    }

    /**
     * @param array $data
     * @param string $key
     */
    public function info(array $data = [], $key = 'default')
    {
        $this->append(Type::INFO, $key, $data);
    }

    /**
     * @param array $data
     * @param string $key
     */
    public function warning(array $data = [], $key = 'default')
    {
        $this->append(Type::WARNING, $key, $data);
    }

    /**
     * @param array $data
     * @param string $key
     */
    public function error(array $data = [], $key = 'default')
    {
        $this->append(Type::ERROR, $key, $data);
    }

}