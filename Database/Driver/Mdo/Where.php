<?php

namespace Yonna\Database\Driver\Mdo;

use Closure;

/**
 * Class Where
 */
class Where
{

    /**
     * filter -> where
     * @var array
     */
    protected array $filter = [];

    /**
     * where条件对象，实现闭包
     * @var array
     */
    private array $closure = [];

    /**
     * where 条件类型设置
     */
    const equalTo = 'equalTo';                              //等于
    const notEqualTo = 'notEqualTo';                        //不等于
    const greaterThan = 'greaterThan';                      //大于
    const greaterThanOrEqualTo = 'greaterThanOrEqualTo';    //大于等于
    const lessThan = 'lessThan';                            //小于
    const lessThanOrEqualTo = 'lessThanOrEqualTo';          //小于等于
    const regex = 'regex';                                  //正则
    const like = 'like';                                    //包含
    const notLike = 'notLike';                              //不包含
    const isNull = 'isNull';                                //为空
    const isNotNull = 'isNotNull';                          //不为空
    const in = 'in';                                        //在或集
    const notIn = 'notIn';                                  //不在或集

    /**
     * where 映射map
     */
    const operatVector = [
        self::equalTo => '$eq',
        self::notEqualTo => '$neq',
        self::greaterThan => '$gt',
        self::greaterThanOrEqualTo => '$gte',
        self::lessThan => '$lt',
        self::lessThanOrEqualTo => '$lte',
        self::regex => '$regex',
        self::like => '$regex',
        self::notLike => '$regex',
        self::isNull => null,
        self::isNotNull => '$ne',
        self::in => '$in',
        self::notIn => '$nin',
    ];

    /**
     * @param string $operat see self
     * @param string $field
     * @param null $value
     * @return $this
     */
    protected function where($operat, $field, $value = null)
    {
        if ($operat == self::isNull || $operat == self::isNotNull || $value !== null) {//排除空值
            if ($operat != self::like || $operat != self::notLike || ($value != '%' && $value != '%%')) {//排除空like
                $this->closure[] = [
                    'type' => 'chip',
                    'operat' => $operat,
                    'field' => $field,
                    'value' => $value,
                ];
            }
        }
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
     * @return self
     */
    public function notEqualTo($field, $value)
    {
        return $this->where(self::notEqualTo, $field, $value);
    }

    /**
     * @param $field
     * @param $value
     * @return self
     */
    public function greaterThan($field, $value)
    {
        return $this->where(self::greaterThan, $field, $value);
    }

    /**
     * @param $field
     * @param $value
     * @return self
     */
    public function greaterThanOrEqualTo($field, $value)
    {
        return $this->where(self::greaterThanOrEqualTo, $field, $value);
    }

    /**
     * @param $field
     * @param $value
     * @return self
     */
    public function lessThan($field, $value)
    {
        return $this->where(self::lessThan, $field, $value);
    }

    /**
     * @param $field
     * @param $value
     * @return self
     */
    public function lessThanOrEqualTo($field, $value)
    {
        return $this->where(self::lessThanOrEqualTo, $field, $value);
    }

    /**
     * @param $field
     * @param $value
     * @return self
     */
    public function regex($field, $value)
    {
        return $this->where(self::regex, $field, $value);
    }

    /**
     * @param $field
     * @param $value
     * @return self
     */
    public function like($field, $value)
    {
        return $this->where(self::like, $field, $value);
    }

    /**
     * @param $field
     * @param $value
     * @return self
     */
    public function notLike($field, $value)
    {
        return $this->where(self::notLike, $field, $value);
    }

    /**
     * @param $field
     * @return self
     */
    public function isNull($field)
    {
        return $this->where(self::isNull, $field);
    }

    /**
     * @param $field
     * @return self
     */
    public function isNotNull($field)
    {
        return $this->where(self::isNotNull, $field);
    }

    /**
     * @param $field
     * @param $value
     * @return self
     */
    public function between($field, $value)
    {
        if (is_string($value)) $value = explode(',', $value);
        if (count($value) !== 2) return $this;
        return $this
            ->where(self::greaterThanOrEqualTo, $field, round($value[0], 6))
            ->where(self::lessThanOrEqualTo, $field, round($value[1], 6));
    }

    /**
     * @param $field
     * @param $value
     * @return self
     */
    public function notBetween($field, array $value)
    {
        if (is_string($value)) $value = explode(',', $value);
        if (count($value) !== 2) return $this;
        return $this
            ->where(self::lessThanOrEqualTo, $field, $value[0])
            ->where(self::greaterThanOrEqualTo, $field, $value[1]);
    }

    /**
     * @param $field
     * @param $value
     * @return self
     */
    public function in($field, array $value)
    {
        return $this->where(self::in, $field, $value);
    }

    /**
     * @param $field
     * @param $value
     * @return self
     */
    public function notIn($field, array $value)
    {
        return $this->where(self::notIn, $field, $value);
    }

    /**
     * 清理where条件
     * @return $this
     */
    public function clearWhere()
    {
        $this->closure = [];
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
     * 数组搜索where
     * @param array $where
     * @return $this
     */
    public function search(array $where)
    {
        $this->closure[] = array('type' => 'array', 'value' => $where);
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

    /**
     * 条件nor闭包
     * @param Closure $cells
     * @return $this
     */
    public function nor(Closure $cells)
    {
        $nw = new self();
        $cells($nw);
        $this->closure[] = ['type' => 'closure', 'cond' => 'nor', 'value' => $nw];
        return $this;
    }

}
