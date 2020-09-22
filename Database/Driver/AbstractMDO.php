<?php

namespace Yonna\Database\Driver;

use MongoDB\BSON\ObjectId;
use MongoDB\BSON\Regex;
use MongoDB\Driver\Command;
use MongoDB\Driver\Query;
use MongoDB\Driver\BulkWrite;
use MongoDB\Driver\Exception\BulkWriteException;
use Yonna\Database\Driver\Mdo\Client;
use Yonna\Database\Driver\Mdo\Where;
use Yonna\Throwable\Exception;

/**
 * Class AbstractMDO
 * @package Yonna\Database\Driver
 * @see https://docs.mongodb.com/ecosystem/drivers/
 */
abstract class AbstractMDO extends AbstractDB
{

    /**
     * @var array
     */
    protected array $data = [];

    /**
     * 架构函数 取得模板对象实例
     * @access public
     * @param array $options
     */
    public function __construct(array $options)
    {
        parent::__construct($options);
    }

    /**
     * 析构函数
     */
    public function __destruct()
    {
        parent::__destruct();
    }

    /**
     * 获取 MDO
     * @return Client
     */
    protected function mdo()
    {
        return $this->malloc();
    }

    /**
     * @return string
     */
    public function getCollection()
    {
        return $this->options['collection'];
    }

    /**
     * where-value分析
     * @access protected
     * @param $field
     * @param mixed $value
     * @return string
     */
    protected function parseWhereValue($field, $value)
    {
        if ($field === "_id") {
            $value = new ObjectId($value);
        } elseif (is_array($value)) {
            foreach ($value as $k => $v) {
                $field[$k] = $this->parseWhereValue($k, $v);
            }
        }
        return $value;
    }

    /**
     * 构建where的Filter句
     * @param $closure
     * @param array $filter
     * @param string $cond
     * @return array
     * @throws Exception\Error\DatabaseException
     */
    private function builtFilter($closure, $filter = [], $cond = 'and')
    {
        foreach ($closure as $v) {
            switch ($v['type']) {
                case 'closure':
                    $filter = $this->builtFilter($v['value']->getClosure(), $filter, $v['cond']);
                    break;
                case 'array':
                    foreach ($v['value'] as $ka => $va) {
                        $va = $this->parseWhereValue($ka, $va);
                        $filter[$ka] = $va;
                    }
                    break;
                case 'chip':
                default:
                    if (!isset($filter[$v['field']])) {
                        $filter[$v['field']] = [];
                    }
                    $value = $this->parseWhereValue($v['field'], $v['value']);
                    switch ($v['operat']) {
                        case Where::regex:
                            $value = new Regex($value);
                            break;
                        case Where::like:
                        case Where::notLike:
                            $t = substr($value, 0, 1) === '%';
                            $e = substr($value, -1) === '%';
                            if ($t && $e) {
                                $value = substr($value, 1, strlen($value) - 2);
                            } elseif ($t) {
                                $value = substr($value, 1);
                                $value = "{$value}$";
                            } elseif ($e) {
                                $value = substr($value, 0, strlen($value) - 1);
                                $value = "^{$value}";
                            }
                            $value = new Regex($value);
                            break;
                        case Where::isNull:
                        case Where::isNotNull:
                            $value = null;
                            break;
                        default:
                            break;
                    }
                    if (strpos('not', strtolower($v['operat'])) !== false) {
                        $value = ['$not' => $value];
                    }
                    if ($v['operat'] === Where::isNull) {
                        $filter[$v['field']] = $value;
                    } else {
                        $filter[$v['field']][Where::operatVector[$v['operat']]] = $value;
                    }
                    break;
            }
        }
        $f = [];
        foreach ($filter as $kf => $vf) {
            $f[] = [$kf => $vf];
        }
        return ["\${$cond}" => $f];
    }

    /**
     * where分析
     * 这个where需要被继承的where覆盖才会有效
     * @return array
     * @throws Exception\Error\DatabaseException
     */
    protected function parseWhere()
    {
        if (empty($this->options['where'])) {
            return [];
        }
        return $this->builtFilter($this->options['where']);
    }

    /**
     * value分析
     * @access protected
     * @param $filter
     * @return string
     */
    protected function getFilterStr($filter)
    {
        if (!$filter) {
            return '{}';
        }
        $str = json_encode($filter, JSON_UNESCAPED_UNICODE);
        preg_match_all('/{"\$oid":"(.*)"}/', $str, $match);
        if ($match[0]) {
            foreach ($match[0] as $mk => $m) {
                $str = str_replace($m, 'ObjectId("__YONNA_MONGO_OBJECT_FUNC__")', $str);
                $str = str_replace('__YONNA_MONGO_OBJECT_FUNC__', $match[1][$mk], $str);
            }
        }
        return $str;
    }

    /**
     * @param $cursor
     * @return array
     */
    private function resultFormat($cursor)
    {
        $result = [];
        foreach ($cursor as $doc) {
            $temp = [];
            $doc = (array)$doc;
            foreach ($doc as $field => $d) {
                if ($field === "_id") {
                    $_id = $d->jsonSerialize();
                    $temp['_id'] = $_id['$oid'];
                } else {
                    $temp[$this->getCollection() . '_' . $field] = $d;
                }
            }
            $result[] = $temp;
        }
        return $result;
    }

    /**
     * 设置执行命令
     * @param $command
     * @return mixed
     * @throws Exception\Error\DatabaseException
     */
    protected function query($command)
    {
        $result = null;
        $commandStr = "un know command";

        $mdoOps = [];
        $session = $this->mdo()->getSession();
        if ($session) {
            $mdoOps['session'] = $session;
        }

        try {
            switch ($command) {
                case 'drop':
                    $command = new Command([
                        'drop' => $this->options['collection'],
                    ]);
                    $res = $this->mdo()->getManager()->executeCommand($this->name, $command);
                    $res = current($res->toArray());
                    $result = $res->ok === 1;
                    $commandStr = "db.{$this->options['collection']}.drop()";
                    break;
                case 'count':
                    $filter = $this->parseWhere();
                    $command = new Command([
                        'count' => $this->options['collection'],
                        'query' => $filter ?: null,
                    ]);
                    $res = $this->mdo()->getManager()->executeCommand($this->name, $command);
                    $res = current($res->toArray());
                    $result = $res->n;
                    $commandStr = "db.{$this->options['collection']}.find(";
                    $commandStr .= $this->getFilterStr($filter);
                    $commandStr .= ').count()';
                    break;
                case 'select':
                    $filter = $this->parseWhere();
                    $query = new Query($filter, $this->options);
                    $cursor = $this->mdo()->getManager()->executeQuery($this->name . '.' . $this->options['collection'], $query);
                    $result = $this->resultFormat($cursor);
                    $projectionStr = empty($this->options['projection']) ? '' : ',' . json_encode($this->options['projection']);
                    $sortStr = empty($this->options['sort']) ? '' : '.sort(' . json_encode($this->options['sort']) . ')';
                    $limitStr = empty($this->options['limit']) ? '' : '.limit(' . json_encode($this->options['limit']) . ')';
                    $skipStr = empty($this->options['skip']) ? '' : '.skip(' . json_encode($this->options['skip']) . ')';
                    $commandStr = "db.{$this->options['collection']}.find(";
                    $commandStr .= $this->getFilterStr($filter) . $projectionStr;
                    $commandStr .= ')';
                    $commandStr .= $sortStr . $limitStr . $skipStr;
                    break;
                case 'insert':
                    if (empty($this->data)) {
                        return false;
                    }
                    $bulk = new BulkWrite();
                    $bulk->insert($this->data);
                    $result = $this->mdo()->getManager()->executeBulkWrite($this->name . '.' . $this->options['collection'], $bulk, $mdoOps);
                    $result = [
                        'ids' => $result->getUpsertedIds(),
                        'insert_count' => $result->getInsertedCount(),
                        'bulk_count' => $bulk->count(),
                    ];
                    $commandStr = "db.{$this->options['collection']}.insertOne(" . json_encode($this->data, JSON_UNESCAPED_UNICODE) . ')';
                    break;
                case 'insertAll':
                    if (empty($this->data)) {
                        return false;
                    }
                    $bulk = new BulkWrite();
                    foreach ($this->data as $d) {
                        $bulk->insert($d);
                    }
                    $result = $this->mdo()->getManager()->executeBulkWrite($this->name . '.' . $this->options['collection'], $bulk, $mdoOps);
                    $result = [
                        'insert_count' => $result->getInsertedCount(),
                        'bulk_count' => $bulk->count(),
                    ];
                    $commandStr = "db.{$this->options['collection']}.insertMany(" . json_encode($this->data, JSON_UNESCAPED_UNICODE) . ')';
                    break;
                case 'update':
                    if (empty($this->data)) {
                        return false;
                    }
                    $filter = $this->parseWhere();
                    $bulk = new BulkWrite();
                    $bulk->update($filter, ['$set' => $this->data], [
                        'multi' => true
                    ]);
                    $result = $this->mdo()->getManager()->executeBulkWrite($this->name . '.' . $this->options['collection'], $bulk, $mdoOps);
                    $result = [
                        'update_count' => $result->getUpsertedCount(),
                        'bulk_count' => $bulk->count(),
                    ];
                    $commandStr = "db.{$this->options['collection']}.update("
                        . $this->getFilterStr($filter) . ',{$set:'
                        . json_encode($this->data, JSON_UNESCAPED_UNICODE) . ',{multi:true})';
                    break;
                case 'delete':
                    $filter = $this->parseWhere();
                    $bulk = new BulkWrite();
                    $bulk->delete($filter, [
                        'limit' => false
                    ]);
                    $result = $this->mdo()->getManager()->executeBulkWrite($this->name . '.' . $this->options['collection'], $bulk, $mdoOps);
                    $result = [
                        'delete_count' => $result->getDeletedCount(),
                        'bulk_count' => $bulk->count(),
                    ];
                    $commandStr = "db.{$this->options['collection']}.delete("
                        . $this->getFilterStr($filter) . ',{limit:false})';
                    break;
            }
        } catch (BulkWriteException $e) {
            Exception::database($e->getMessage());
        } catch (\MongoDB\Driver\Exception\Exception $e) {
            Exception::database($e->getMessage());
        }
        parent::query($commandStr);
        return $result;
    }

}
