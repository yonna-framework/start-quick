<?php

namespace Yonna\QuickStart\Scope\User;

use Yonna\QuickStart\Mapping\Common\Boolean;
use Yonna\QuickStart\Prism\UserMetaCategoryPrism;
use Yonna\QuickStart\Scope\AbstractScope;
use Yonna\Database\DB;
use Yonna\Database\Driver\Pdo\Where;
use Yonna\Throwable\Exception;
use Yonna\Validator\ArrayValidator;

/**
 * Class Meta
 * @package Yonna\QuickStart\Scope\User
 */
class MetaCategory extends AbstractScope
{

    const TABLE = 'user_meta_category';

    /**
     * @return mixed
     * @throws Exception\DatabaseException
     */
    public function one(): array
    {
        ArrayValidator::required($this->input(), ['id'], function ($error) {
            Exception::throw($error);
        });
        return DB::connect()->table(self::TABLE)
            ->where(fn(Where $w) => $w->equalTo('id', $this->input('id')))
            ->one();
    }

    /**
     * @return mixed
     * @throws Exception\DatabaseException
     */
    public function multi(): array
    {
        $prism = new UserMetaCategoryPrism($this->request());
        return DB::connect()->table(self::TABLE)
            ->where(function (Where $w) use ($prism) {
                $prism->getKey() && $w->equalTo('key', $prism->getKey());
                $prism->getStatus() && $w->equalTo('status', $prism->getStatus());
            })
            ->orderBy('sort', 'desc')
            ->multi();
    }

    /**
     * @return mixed
     * @throws Exception\DatabaseException
     */
    public function page(): array
    {
        $prism = new UserMetaCategoryPrism($this->request());
        return DB::connect()->table(self::TABLE)
            ->where(function (Where $w) use ($prism) {
                $prism->getKey() && $w->equalTo('key', $prism->getKey());
                $prism->getStatus() && $w->equalTo('status', $prism->getStatus());
            })
            ->orderBy('sort', 'desc')
            ->page($prism->getCurrent(), $prism->getPer());
    }

    /**
     * @return int
     * @throws Exception\DatabaseException
     */
    public function insert()
    {
        ArrayValidator::required($this->input(), ['key', 'value_format'], function ($error) {
            Exception::throw($error);
        });
        $add = [
            'key' => $this->input('key'),
            'value_format' => $this->input('value_format'),
            'value_default' => $this->input('value_default') ?? '',
            'status' => $this->input('status') ?? Boolean::false,
            'sort' => $this->input('sort') ?? 0,
        ];
        return DB::connect()->table(self::TABLE)->insert($add);
    }

    /**
     * @return int
     * @throws Exception\DatabaseException
     */
    public function update()
    {
        ArrayValidator::required($this->input(), ['key'], function ($error) {
            Exception::throw($error);
        });
        $data = [
            'value_format' => $this->input('value_format'),
            'value_default' => $this->input('value_default'),
            'status' => $this->input('status'),
            'sort' => $this->input('sort'),
        ];
        if ($data) {
            return DB::connect()->table(self::TABLE)
                ->where(fn(Where $w) => $w->equalTo('key', $this->input('key')))
                ->update($data);
        }
        return true;
    }

    /**
     * @return int
     * @throws Exception\DatabaseException
     */
    public function delete()
    {
        ArrayValidator::required($this->input(), ['key'], function ($error) {
            Exception::throw($error);
        });
        return DB::connect()->table(self::TABLE)
            ->where(fn(Where $w) => $w->equalTo('key', $this->input('key')))
            ->delete();
    }

    /**
     * @return int
     * @throws Exception\DatabaseException
     */
    public function multiStatus()
    {
        ArrayValidator::required($this->input(), ['keys', 'status'], function ($error) {
            Exception::throw($error);
        });
        return DB::connect()->table(self::TABLE)
            ->where(fn(Where $w) => $w->in('key', $this->input('keys')))
            ->update(["status" => $this->input('status')]);
    }

}