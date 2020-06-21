<?php

namespace Yonna\Database\Driver\Pdo;

use Closure;

/**
 * Class Where
 */
class Where
{

    /**
     * where条件对象，实现闭包
     * @var array
     */
    protected array $closure = [];

    /**
     * where条件，哪个表
     * @var string|null
     */
    protected ?string $search_table = null;

    /**
     * where 条件类型设置
     */
    const equalTo = 'equalTo';                              //等于
    const notEqualTo = 'notEqualTo';                        //不等于
    const greaterThan = 'greaterThan';                      //大于
    const greaterThanOrEqualTo = 'greaterThanOrEqualTo';    //大于等于
    const lessThan = 'lessThan';                            //小于
    const lessThanOrEqualTo = 'lessThanOrEqualTo';          //小于等于
    const like = 'like';                                    //包含
    const notLike = 'notLike';                              //不包含
    const isNull = 'isNull';                                //为空
    const isNotNull = 'isNotNull';                          //不为空
    const between = 'between';                              //在值之内
    const notBetween = 'notBetween';                        //在值之外
    const in = 'in';                                        //在或集
    const notIn = 'notIn';                                  //不在或集
    const findInSet = 'findInSet';                          //findInSet (mysql)
    const notFindInSet = 'notFindInSet';                    //notFindInSet (mysql)
    const any = 'any';                                      //any (pgsql)
    const contains = 'contains';                            //contains (pgsql)
    const isContainsBy = 'isContainsBy';                    //isContainsBy (pgsql)

    /**
     * @param string $operat see self
     * @param string $field
     * @param null $value
     * @return $this
     */
    private function where($operat, $field, $value = null)
    {
        if ($operat == self::isNull || $operat == self::isNotNull || $value !== null) {//排除空值
            if ($operat != self::like || $operat != self::notLike || ($value != '%' && $value != '%%')) {//排除空like
                $this->closure[] = [
                    'type' => 'chip',
                    'operat' => $operat,
                    'table' => $this->search_table,
                    'field' => $field,
                    'value' => $value,
                ];
            }
        }
        return $this;
    }

    /**
     * 清理where条件
     * @return $this
     */
    public function clearWhere()
    {
        $this->closure = [];
        $this->search_table = '';
        return $this;
    }

    /**
     * 获取条件闭包
     * @return array
     */
    public function getClosure()
    {
        return $this->closure;
    }

    /**
     * 锁定为哪一个表的搜索目标
     * @param $table
     * @return $this
     */
    public function searchTable($table)
    {
        $this->search_table = $table;
        return $this;
    }

    /**
     * @param $field
     * @param $value
     * @return $this
     */
    public function equalTo($field, $value)
    {
        return $this->where(self::equalTo, $field, $value);
    }

    /**
     * @param $field
     * @param $value
     * @return $this
     */
    public function notEqualTo($field, $value)
    {
        return $this->where(self::notEqualTo, $field, $value);
    }

    /**
     * @param $field
     * @param $value
     * @return $this
     */
    public function greaterThan($field, $value)
    {
        return $this->where(self::greaterThan, $field, $value);
    }

    /**
     * @param $field
     * @param $value
     * @return $this
     */
    public function greaterThanOrEqualTo($field, $value)
    {
        return $this->where(self::greaterThanOrEqualTo, $field, $value);
    }

    /**
     * @param $field
     * @param $value
     * @return $this
     */
    public function lessThan($field, $value)
    {
        return $this->where(self::lessThan, $field, $value);
    }

    /**
     * @param $field
     * @param $value
     * @return $this
     */
    public function lessThanOrEqualTo($field, $value)
    {
        return $this->where(self::lessThanOrEqualTo, $field, $value);
    }

    /**
     * @param $field
     * @param $value
     * @return $this
     */
    public function like($field, $value)
    {
        return $this->where(self::like, $field, $value);
    }

    /**
     * @param $field
     * @param $value
     * @return $this
     */
    public function notLike($field, $value)
    {
        return $this->where(self::notLike, $field, $value);
    }

    /**
     * @param $field
     * @return $this
     */
    public function isNull($field)
    {
        return $this->where(self::isNull, $field);
    }

    /**
     * @param $field
     * @return $this
     */
    public function isNotNull($field)
    {
        return $this->where(self::isNotNull, $field);
    }

    /**
     * @param $field
     * @param $value
     * @return $this
     */
    public function between($field, $value)
    {
        if (is_string($value)) $value = explode(',', $value);
        if (!is_array($value)) $value = (array)$value;
        if (count($value) !== 2) return $this;
        if (!$value[0]) return $this;
        if (!$value[1]) return $this;
        return $this->where(self::between, $field, $value);
    }

    /**
     * @param $field
     * @param $value
     * @return $this
     */
    public function notBetween($field, $value)
    {
        if (is_string($value)) $value = explode(',', $value);
        if (!is_array($value)) $value = (array)$value;
        if (count($value) !== 2) return $this;
        return $this->where(self::notBetween, $field, $value);
    }

    /**
     * @param $field
     * @param $value
     * @return $this
     */
    public function in($field, $value)
    {
        return $this->where(self::in, $field, $value);
    }

    /**
     * @param $field
     * @param $value
     * @return $this
     */
    public function notIn($field, $value)
    {
        return $this->where(self::notIn, $field, $value);
    }

    /**
     * @param $field
     * @param $value
     * @return $this
     */
    public function findInSet($field, $value)
    {
        return $this->where(self::findInSet, $field, $value);
    }

    /**
     * @param $field
     * @param $value
     * @return $this
     */
    public function notFindInSet($field, $value)
    {
        return $this->where(self::notFindInSet, $field, $value);
    }

    /**
     * @param array $set
     * @param array $data
     * @return $this
     */
    public function complex(array $set, array $data)
    {
        foreach ($set as $target => $actions) {
            $this->searchTable($target);
            foreach ($actions as $action) {
                foreach ($set as $field) {
                    if (!isset($whereData[$field]) || $data[$field] === null) {
                        continue;
                    }
                    if ($data[$field] !== null) {
                        switch ($action) {
                            case 'like':
                                $this->$action('%' . $data[$field] . '%');
                                break;
                            default:
                                $this->$action($data[$field]);
                                break;
                        }
                        $this->$action($data[$field]);
                    }
                }
            }
        }
        return $this;
    }

    /**
     * 字符串搜索where
     * @param string $where
     * @return $this
     */
    public function search(string $where)
    {
        $this->closure[] = array('type' => 'string', 'value' => $where);
        return $this;
    }

    /**
     * 条件and闭包
     * @param Closure $cells
     * @return $this
     */
    public function and(Closure $cells)
    {
        $nw = new self();
        $cells($nw);
        $this->closure[] = ['type' => 'closure', 'cond' => 'and', 'value' => $nw];
        return $this;
    }

    /**
     * 条件or闭包
     * @param Closure $cells
     * @return $this
     */
    public function or(Closure $cells)
    {
        $nw = new self();
        $cells($nw);
        $this->closure[] = ['type' => 'closure', 'cond' => 'or', 'value' => $nw];
        return $this;
    }

}
