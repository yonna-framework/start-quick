<?php
/**
 * 数据库连接构建类，依赖 MongoDB
 * mongo version >= 4
 */

namespace Yonna\Database\Driver\Mdo;

use Closure;
use Yonna\Database\Driver\AbstractMDO;
use Yonna\Throwable\Exception;

class Collection extends AbstractMDO
{

    /**
     * 构造方法
     *
     * @param array $options
     */
    public function __construct(array $options)
    {
        parent::__construct($options);
    }

    /**
     * @param string $type
     * @param string $secret
     * @param string $iv
     * @return $this
     */
    public function crypto(string $type, string $secret, string $iv)
    {
        return parent::crypto($type, $secret, $iv);
    }

    /**
     * 限定字段
     * @param $field
     * @param bool $except 排除模式
     * @return $this
     */
    public function field($field, $except = false): self
    {
        if (is_string($field)) {
            $field = explode(',', $field);
        }
        $this->options['projection'] = [];
        if (is_array($field)) {
            $field = array_filter($field);
            foreach ($field as $f) {
                $f = trim($f);
                $this->options['projection'][] = [$f => $except ? 0 : 1];
            }
        }
        return $this;
    }

    /**
     * @param $group
     * @return self
     */
    public function groupBy($group): self
    {
        $this->options['aggregate'] = $group;
        return $this;
    }

    /**
     * @param $orderBy
     * @param string $sort
     * @return self
     */
    public function orderBy($orderBy, $sort = 'asc'): self
    {
        if (!$orderBy) {
            return $this;
        }
        if (is_string($orderBy)) {
            $orderBy = explode(',', $orderBy);
        }
        if (is_array($orderBy)) {
            $orderBy = array_filter($orderBy);
            foreach ($orderBy as $o) {
                $o = explode(' ', $o);
                if (count($o) > 1) {
                    $o[1] = strtolower($o[1]);
                    $orderBy[$o[0]] = $o[1];
                    $this->options['sort'][$o[0]] = $o[1] === 'asc' ? 1 : -1;
                } else {
                    $this->options['sort'][$o[0]] = $sort === 'asc' ? 1 : -1;
                }
            }
        }
        return $this;
    }

    /**
     * where
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
     * @param int $skip
     * @return self
     */
    public function offset(int $skip): self
    {
        $this->options['skip'] = $skip;
        return $this;
    }

    /**
     * @param int $limit
     * @return self
     */
    public function limit(int $limit): self
    {
        $this->options['limit'] = $limit;
        return $this;
    }

    /**
     * 删除合集
     * @param bool $sure 确认执行，防止误操作
     * @return self
     * @throws Exception\DatabaseException
     */
    public function drop($sure = false)
    {
        if ($this->getCollection() && $sure === true) {
            return $this->query('drop');
        }
        return $this;
    }

    /**
     * @return mixed
     * @throws Exception\DatabaseException
     */
    public function multi()
    {
        return $this->query('select');
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
        $count = $this->count();
        $limit = (int)$per;
        $offset = (int)($current - 1) * $limit;
        $this->offset($offset);
        $this->limit($limit);
        $data = $this->multi();
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
     * @return int
     * @throws Exception\DatabaseException
     */
    public function count()
    {
        return $this->query('count');
    }

    /**
     * insert
     * @param $data
     * @return mixed
     * @throws Exception\DatabaseException
     */
    public function insert($data)
    {
        $this->data = $data;
        return $this->query('insert');
    }

    /**
     * insert all
     * @param $data
     * @return mixed
     * @throws Exception\DatabaseException
     */
    public function insertAll($data)
    {
        $this->data = $data;
        return $this->query('insertAll');
    }

    /**
     * update
     * @param $data
     * @param bool $sure
     * @return mixed
     * @throws Exception\DatabaseException
     */
    public function update($data, $sure = false)
    {
        $where = $this->parseWhere();
        if (!$where && !$sure) {
            Exception::database('Mongo update must be sure when without where');
        }
        $this->data = $data;
        return $this->query('update');
    }

    /**
     * delete
     * @param bool $sure
     * @return mixed
     * @throws Exception\DatabaseException
     */
    public function delete($sure = false)
    {
        $where = $this->parseWhere();
        if (!$where && !$sure) {
            Exception::database('Mongo delete must be sure when without where');
        }
        return $this->query('delete');
    }

}
