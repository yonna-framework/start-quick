<?php

namespace Yonna\Log;


use Exception;
use Throwable;
use Yonna\Database\DB;
use Yonna\Database\Driver\Mongo;
use Yonna\Database\Driver\Mysql;
use Yonna\Database\Driver\Pgsql;
use Yonna\QuickStart\Sql\Log as LogSql;

class DatabaseLog
{

    /**
     * @var string|array
     */
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
     * 初始化数据库
     */
    public function initDatabase()
    {
        $db = DB::connect($this->config);
        try {
            if ($db instanceof Mysql) {
                $db->query(LogSql::mysql);
            } elseif ($db instanceof Pgsql) {
                $db->query(LogSql::pgsql);
            }
        } catch (Throwable $e) {
            Log::file()->throwable($e);
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
                $obj = $db->collection('y_log');
            } elseif ($db instanceof Mysql) {
                $obj = $db->table('y_log');
            } elseif ($db instanceof Pgsql) {
                $obj = $db->schemas('public')->table('y_log');
            } else {
                throw new Exception('Set Database for Support Driver.');
            }
            $obj = $obj->orderBy('record_timestamp', 'desc');
            if (!empty($options['key'])) {
                $obj = $obj->equalTo('key', $options['key']);
            }
            if (!empty($options['type'])) {
                $obj = $obj->equalTo('type', $options['key']);
            }
            if (!empty($options['record_timestamp'])) {
                $obj = $obj->between('record_timestamp', $options['record_timestamp']);
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
            'record_timestamp' => time(),
            'data' => $data,
        ];
        try {
            if ($db instanceof Mongo) {
                $db->collection('y_log')->insert($logData);
            } elseif ($db instanceof Mysql) {
                $db->table('y_log')->insert($logData);
            } elseif ($db instanceof Pgsql) {
                $db->schemas('public')->table('y_log')->insert($logData);
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