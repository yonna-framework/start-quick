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
     * 分页获得数据
     * @param Prism $prism
     * @return array
     */
    public function page(Prism $prism)
    {
        $res = [];
        try {
            $db = DB::connect($this->config);
            if ($db instanceof Mongo) {
                $obj = $db->collection('log');
            } elseif ($db instanceof Mysql) {
                $obj = $db->table('log');
            } elseif ($db instanceof Pgsql) {
                $obj = $db->schemas('public')->table('log');
            } else {
                throw new Exception('Set Database for Support Driver.');
            }
            $obj->orderBy('record_time', 'desc');
            $obj->where(function ($cond) use ($prism) {
                /**
                 * @var \Yonna\Database\Driver\Pdo\Where|\Yonna\Database\Driver\Mdo\Where $cond
                 */
                $prism->getKey() && $cond->equalTo('key', $prism->getKey());
                $prism->getType() && $cond->equalTo('type', $prism->getType());
                $prism->getRecordTime() && $cond->between('record_time', $prism->getRecordTime());
            });
            $res = $obj->page($prism->getCurrent(), $prism->getPer());
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
            'record_time' => time(),
            'data' => $data,
        ];
        try {
            if ($db instanceof Mongo) {
                $db->collection('log')->insert($logData);
            } elseif ($db instanceof Mysql) {
                $db->table('log')->insert($logData);
            } elseif ($db instanceof Pgsql) {
                $db->schemas('public')->table('log')->insert($logData);
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