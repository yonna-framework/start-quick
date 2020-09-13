<?php

namespace Yonna\QuickStart\Scope;

use Yonna\QuickStart\Prism\UserMetaPrism;
use Yonna\Database\DB;
use Yonna\Database\Driver\Pdo\Where;
use Yonna\Throwable\Exception;
use Yonna\Validator\ArrayValidator;

/**
 * Class Meta
 * @package Yonna\QuickStart\Scope\User
 */
class UserMeta extends AbstractScope
{

    const TABLE = 'user_meta';

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
        $prism = new UserMetaPrism($this->request());
        return DB::connect()->table(self::TABLE)
            ->where(function (Where $w) use ($prism) {
                $prism->getUserId() && $w->equalTo('user_id', $prism->getUserId());
                $prism->getKey() && $w->equalTo('key', $prism->getKey());
            })
            ->multi();
    }

}