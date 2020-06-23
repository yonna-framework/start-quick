<?php

namespace Yonna\Database\Driver\Pdo;

use Closure;
use Yonna\Database\Driver\AbstractPDO;
use Yonna\Database\Driver\Type;
use Yonna\Throwable\Exception;

/**
 * Class Table
 * @package Yonna\Database\Driver\Pdo
 */
class Table extends AbstractPDO
{

    /**
     * Table constructor.
     * @param array $options
     * @throws null
     */
    public function __construct(array $options)
    {
        if ($options['db_type'] === Type::PGSQL || $options['db_type'] === Type::MSSQL) {
            $this->options['schemas'] = $options['schemas'] ?? null;
            if ($this->options['schemas'] === null) {
                Exception::database($options['db_type'] . ' should set schemas');
            }
        }
        parent::__construct($options);
    }

    /**
     * 指定查询字段
     * @param mixed $field
     * @param string | null $table
     * @param null $function
     * @return $this
     * @throws Exception\DatabaseException
     */
    public function field($field, $table = null, $function = null)
    {
        parent::field($field, $table, $function);
        return $this;
    }

    /**
     * USING支持 用于多表删除
     * @access public
     * @param mixed $using
     * @return self
     */
    public function using($using)
    {
        if ($using) {
            $this->options['using'] = $using;
        }
        return $this;
    }

    /**
     * @return int
     */
    public function getJoinQty()
    {
        return (int)$this->options['join_qty'];
    }

    /**
     * 查询SQL组装 join
     * @access public
     * @param mixed $join
     * @param string $type JOIN类型
     * @return self
     */
    private function joinTo($join, $type = 'LEFT')
    {
        if (is_array($join)) {
            foreach ($join as $key => &$_join) {
                $_join = false !== stripos($_join, 'JOIN') ? $_join : $type . ' JOIN ' . $_join;
            }
            $this->options['join'] = $join;
        } elseif (!empty($join)) {
            $this->options['join'][] = false !== stripos($join, 'JOIN') ? $join : $type . ' JOIN ' . $join;
        }
        return $this;
    }

    /**
     * @param $target
     * @param $join
     * @param array $req
     * @param string $type INNER | OUTER | LEFT | RIGHT
     * @return self
     */
    public function join($target, $join, $req = array(), $type = 'INNER')
    {
        if ($target && $join) {
            $join = str_replace([' as ', ' AS ', ' As ', ' aS ', ' => '], ' ', trim($join));
            $originJoin = $join = explode(' ', $join);
            $alia = null;
            if (isset($join[1]) && $join[1]) {
                $alia = $this->parseKey($join[1]);
            }
            if (isset($join[0]) && $join[0]) {
                $join = $this->parseKey($join[0]);
            }
            $target = $this->parseKey($target);
            $join = $this->parseKey($join);
            $jsonStr = $join;
            $jsonStr .= $alia ? " AS {$alia}" : "";
            if ($req) {
                $jsonStr .= ' ON ';
                $first = false;
                foreach ($req as $k => $v) {
                    if (!$first) {
                        $first = true;
                        $jsonStr .= $alia ? "{$target}.{$k}={$alia}.{$v}" : "{$target}.{$k}={$join}.{$v}";
                    } else $jsonStr .= " AND " . ($alia ? "{$target}.{$k}={$alia}.{$v}" : '');
                }
            }
            if (!isset($this->options['join_qty'])) {
                $this->options['join_qty'] = 0;
            }
            $this->options['join_qty']++;
            if ($alia) {
                if (!isset($this->options['alia'])) {
                    $this->options['alia'] = [];
                }
                $this->options['alia'][$originJoin[1]] = $originJoin[0];
            }
            $this->joinTo($jsonStr, $type);
        }
        return $this;
    }

    /**
     * group by
     * @param mixed $groupBy
     * @param string | null $table
     * @return self
     */
    public function groupBy($groupBy, $table = null)
    {
        if (is_array($groupBy)) {
            $groupBy = implode(',', $groupBy);
        }
        if (!is_string($groupBy)) {
            return $this;
        }
        if (!isset($this->options['group'])) {
            $this->options['group'] = '';
        }
        if ($this->options['group'] != '') {
            $this->options['group'] .= ',';
        }
        if ($table) {
            $this->options['group'] .= $this->parseTable($table) . '.' . $groupBy;
        } else $this->options['group'] .= $groupBy;
        return $this;
    }

    /**
     * order by
     * @param mixed $orderBy 支持格式 'uid asc' | array('uid asc','pid desc')
     * @param string $sort
     * @param string | null $table
     * @return self
     */
    public function orderBy($orderBy, $sort = self::ASC, $table = null)
    {
        if (!$orderBy) {
            return $this;
        }
        if (!isset($this->options['order'])) {
            $this->options['order'] = [];
        }
        if ($table) {
            $table = $this->parseTable($table);
        }

        if (is_string($orderBy)) {
            $orderBy = explode(',', $orderBy);
        }
        if (is_array($orderBy)) {
            $orderBy = array_filter($orderBy);
            foreach ($orderBy as $o) {
                $o = explode(' ', $o);
                if (count($o) > 1) {
                    $s = strtolower($o[1]);
                } else {
                    $s = $sort;
                }
                if ($table) {
                    $this->options['order'][$table . '.' . $o[0]] = $s;
                } else {
                    $this->options['order'][$o[0]] = $s;
                }
            }
        }
        return $this;
    }

    /**
     * order by string 支持 field asc,field desc 形式
     * @param $orderBy
     * @param null $table
     * @return self
     */
    public function orderByStr($orderBy, $table = null): self
    {
        $orderBy = explode(',', $orderBy);
        foreach ($orderBy as $o) {
            $o = explode(' ', $o);
            if ($table) {
                $this->options['order'][$table . '.' . $o[0]] = $o[1];
            } else {
                $this->options['order'][$o[0]] = $o[1];
            }
        }
        return $this;
    }

    /**
     * having
     * @access protected
     * @param mixed $having
     * @param string | null $table
     * @return self
     */
    public function having($having, $table = null)
    {
        if (!is_string($having)) {
            return $this;
        }
        if (!isset($this->options['having'])) {
            $this->options['having'] = '';
        }
        if ($this->options['having'] != '') {
            $this->options['having'] .= ',';
        }
        if ($table) {
            $this->options['having'] .= $this->$having($table) . '.' . $having;
        } else {
            $this->options['having'] .= $having;
        }
        return $this;
    }

    /**
     * where
     * Multiple use will be cover
     * @param Closure $condition
     * @return self
     */
    public function where(Closure $condition)
    {
        $where = new Where();
        $condition($where);
        $this->options['where'] = $where->getClosure();
        return $this;
    }

    /**
     * 指定查询数量
     * @access protected
     * @param mixed $offset 起始位置
     * @param mixed $length 查询数量
     * @return self
     */
    public function limit($offset, $length = null)
    {
        if (is_null($length) && strpos($offset, ',')) {
            list($offset, $length) = explode(',', $offset);
        }
        switch ($this->options['db_type']) {
            case Type::MSSQL:
                if ($length === null) {
                    $this->options['limit'] = $offset;
                    $this->options['offset'] = null;
                } else {
                    $this->options['limit'] = $length;
                    $this->options['offset'] = $offset;
                }
                break;
            default:
                $this->options['limit'] = ($length ? intval($length) . ' OFFSET ' : '') . intval($offset);
                break;
        }
        return $this;
    }

    /**
     * 查找记录多条
     * @access public
     * @return mixed
     * @throws Exception\DatabaseException
     */
    public function multi()
    {
        $sql = $this->buildSelectSql();
        return $this->query($sql);
    }

    /**
     * 查找记录一条
     * @return mixed
     * @throws Exception\DatabaseException
     */
    public function one()
    {
        $this->limit(1);
        $result = $this->multi();
        return $result && is_array($result) ? reset($result) : $result;
    }

    /**
     * 分页查找
     * @param int $current
     * @param int $per
     * @return mixed
     * @throws Exception\DatabaseException
     */
    public function page($current = 1, $per = 10)
    {
        $limit = (int)$per;
        $offset = (int)($current - 1) * $limit;
        $this->limit($offset, $limit);

        $sql = $this->buildSelectSql();
        $this->options['order'] = null;
        $this->options['limit'] = 1;
        $this->options['offset'] = 0;
        if (!empty($this->options['group'])) {
            $this->options['field'] = 'count(DISTINCT ' . $this->options['group'] . ') as "hcount"';
            $this->options['group'] = null;
        } else {
            $this->options['field'] = 'count(0) as "hcount"';
        }
        $sqlCount = $this->buildSelectSql();
        $data = $this->query($sql);
        if ($this->options['fetch_query'] == true) {
            return $data;
        }
        $count = $this->query($sqlCount);
        $count = reset($count)['hcount'];
        $count = (int)$count;
        $result = [];
        $per = !$per ? 10 : $per;
        $last = ceil($count / $per);
        $result['list'] = $data;
        $result['page']['total'] = $count;
        $result['page']['per'] = $per;
        $result['page']['current'] = (int)$current;
        $result['page']['last'] = (int)$last;
        return $result;
    }

    /**
     * 统计
     * @param int $field
     * @return int
     * @throws Exception\DatabaseException
     */
    public function count($field = 0)
    {
        $this->field("COUNT(" . ($field === 0 ? '0' : $this->parseKey($field)) . ") AS \"hcount\"");
        $result = $this->one();
        return (int)$result['hcount'];
    }

    /**
     * 求和
     * @param string $field
     * @return int
     * @throws Exception\DatabaseException
     */
    public function sum($field)
    {
        $this->field("SUM(" . $this->parseKey($field) . ") AS \"hsum\"");
        $result = $this->one();
        return round($result['hsum'], 10);
    }

    /**
     * 求均
     * @param $field
     * @return int
     * @throws Exception\DatabaseException
     */
    public function avg($field)
    {
        $this->field("AVG(" . $this->parseKey($field) . ") AS \"havg\"");
        $result = $this->one();
        return round($result['havg'], 10);
    }

    /**
     * 求最小
     * @param $field
     * @return int
     * @throws Exception\DatabaseException
     */
    public function min($field)
    {
        $this->field("MIN(" . $this->parseKey($field) . ") AS \"hmin\"");
        $result = $this->one();
        return round($result['hmin'], 10);
    }

    /**
     * 求最大
     * @param $field
     * @return int
     * @throws Exception\DatabaseException
     */
    public function max($field)
    {
        $this->field("MAX(" . $this->parseKey($field) . ") AS \"hmax\"");
        $result = $this->one();
        return round($result['hmax'], 10);
    }

    /**
     * 插入记录
     * @access public
     * @param mixed $data 数据
     * @return integer
     * @throws Exception\DatabaseException
     */
    public function insert($data)
    {
        $values = $fields = [];
        $table = $this->getTable();
        $ft = $this->getFieldType($table);
        foreach ($data as $key => $val) {
            if (!empty($ft[$table . '_' . $key])) { // 根据表字段过滤无效key
                if (is_array($val) && isset($val[0]) && 'exp' == $val[0]) {
                    $fields[] = $this->parseKey($key);
                    $values[] = $val[1] ?? null;
                } elseif (is_null($val)) {
                    $fields[] = $this->parseKey($key);
                    $values[] = 'NULL';
                } elseif (is_array($val) || is_scalar($val)) { // 过滤非标量数据
                    // 跟据表字段处理数据
                    if (is_array($val) && strpos($ft[$table . '_' . $key], 'char') !== false) { // 字符串型数组
                        $val = $this->arr2comma($val, $ft[$table . '_' . $key]);
                    } else {
                        $val = $this->parseValueByFieldType($val, $ft[$table . '_' . $key]);
                    }
                    if ($val !== null) {
                        $fields[] = $this->parseKey($key);
                        $values[] = $this->parseValue($val);
                    }
                }
            }
        }
        // 兼容数字传入方式
        $sql = 'INSERT INTO ' . $this->parseTable($table) . ' (' . implode(',', $fields) . ') VALUES (' . implode(',', $values) . ')';
        $sql .= $this->parseComment(!empty($options['comment']) ? $options['comment'] : '');
        return $this->query($sql);
    }

    /**
     * 批量插入记录
     * @access public
     * @param mixed $dataSet 数据集
     * @return false | integer
     * @throws Exception\DatabaseException
     */
    public function insertAll($dataSet)
    {
        $values = [];
        if (!is_array($dataSet[0])) {
            return false;
        }
        $fields = array_map(array($this, 'parseKey'), array_keys($dataSet[0]));
        $table = $this->getTable();
        $ft = $this->getFieldType($table);
        foreach ($dataSet as $data) {
            $value = [];
            foreach ($data as $key => $val) {
                if (!empty($ft[$table . '_' . $key])) { // 根据表字段过滤无效key
                    if (is_array($val) && isset($val[0]) && 'exp' == $val[0]) {
                        $value[] = $val[1];
                    } elseif (is_null($val)) {
                        $value[] = 'NULL';
                    } elseif (is_array($val) || is_scalar($val)) { // 过滤非标量数据
                        // 跟据表字段处理数据
                        if (is_array($val) && strpos($ft[$table . '_' . $key], 'char') !== false) { // 字符串型数组
                            $val = $this->arr2comma($val, $ft[$table . '_' . $key]);
                            if ($val === null) $value[] = 'NULL';
                        } else {
                            $val = $this->parseValueByFieldType($val, $ft[$table . '_' . $key]);
                        }
                        if ($val !== null) {
                            $value[] = $this->parseValue($val);
                        }
                    }
                }
            }
            $values[] = '(' . implode(',', $value) . ')';
        }
        $sql = 'INSERT INTO ' . $this->parseTable($table) . ' (' . implode(',', $fields) . ') VALUES ' . implode(' , ', $values);
        $sql .= $this->parseComment(!empty($options['comment']) ? $options['comment'] : '');
        return $this->query($sql);
    }

    /**
     * 更新记录
     * @access public
     * @param mixed $data 数据
     * @param bool $sure
     * @return false | integer
     * @throws Exception\DatabaseException
     */
    public function update($data, $sure = false)
    {
        $table = $this->getTable();
        $sql = 'UPDATE ' . $this->parseTable($table);
        $ft = $this->getFieldType($table);
        $set = [];
        foreach ($data as $key => $val) {
            if (!empty($ft[$table . '_' . $key])) { // 根据表字段过滤无效key
                if (is_array($val) && !empty($val[0]) && 'exp' == $val[0]) {
                    $set[] = $this->parseKey($key) . '=' . $val[1];
                } elseif (is_null($val)) {
                    $set[] = $this->parseKey($key) . '= NULL';
                } elseif (is_array($val) || is_scalar($val)) { // 过滤非标量数据
                    // 跟据表字段处理数据
                    if (is_array($val) && strpos($ft[$table . '_' . $key], 'char') !== false) { // 字符串型数组
                        $val = $this->arr2comma($val, $ft[$table . '_' . $key]);
                    } else {
                        $val = $this->parseValueByFieldType($val, $ft[$table . '_' . $key]);
                    }
                    if ($val !== null) {
                        $set[] = $this->parseKey($key) . '=' . $this->parseValue($val);
                    }
                }
            }
        }
        $sql .= ' SET ' . implode(',', $set);
        if (strpos($table, ',')) {// 多表更新支持JOIN操作
            $sql .= $this->parseJoin(!empty($this->options['join']) ? $this->options['join'] : '');
        }
        $where = $this->parseWhere();
        if (!$where && $sure !== true) {
            Exception::database('update must be sure when without where：' . $sql);
        }
        $sql .= $where;
        if (!strpos($table, ',')) {
            // 单表更新支持order和limit
            $sql .= $this->parseOrderBy(!empty($this->options['order']) ? $this->options['order'] : '')
                . $this->parseLimit(!empty($this->options['limit']) ? $this->options['limit'] : '');
        }
        $sql .= $this->parseComment(!empty($this->options['comment']) ? $this->options['comment'] : '');
        return $this->query($sql);
    }

    /**
     * 删除记录
     * @access public
     * @param bool $sure
     * @return false | integer
     * @throws Exception\DatabaseException
     */
    public function delete($sure = false)
    {
        $table = $this->parseTable($this->options['table']);
        $sql = 'DELETE FROM ' . $table;
        if (strpos($table, ',')) {
            // 多表删除支持USING和JOIN操作
            if (!empty($this->options['using'])) {
                $sql .= ' USING ' . $this->parseTable($this->options['using']) . ' ';
            }
            $sql .= $this->parseJoin(!empty($this->options['join']) ? $this->options['join'] : '');
        }
        $where = $this->parseWhere();
        if (!$where && $sure !== true) {
            Exception::database('delete must be sure when without where');
        }
        $sql .= $where;
        if (!strpos($table, ',')) {
            // 单表删除支持order和limit
            $sql .= $this->parseOrderBy(!empty($this->options['order']) ? $this->options['order'] : '')
                . $this->parseLimit(!empty($this->options['limit']) ? $this->options['limit'] : '');
        }
        $sql .= $this->parseComment(!empty($this->options['comment']) ? $this->options['comment'] : '');
        return $this->query($sql);
    }

    /**
     * 截断表
     * @alert 必须注意，这个方法一经执行会“清空”原来的“所有数据”及“自增量”
     * @param bool $sure 确认执行，防止误操作
     * @return self
     * @throws Exception\DatabaseException
     */
    public function truncate($sure = false)
    {
        if ($this->getTable() && $sure === true) {
            $sqlStr = "TRUNCATE TABLE " . $this->parseTable($this->getTable());
            return $this->query($sqlStr);
        }
        return $this;
    }

    //

}