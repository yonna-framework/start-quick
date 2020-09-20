<?php

namespace Yonna\QuickStart\Scope;

use Yonna\Database\DB;
use Yonna\Database\Driver\Pdo\Where;
use Yonna\QuickStart\Prism\SdkPrism;
use Yonna\Throwable\Exception;
use Yonna\Validator\ArrayValidator;

/**
 * Class Sdk
 * @package Yonna\QuickStart\Scope
 */
class Sdk extends AbstractScope
{

    const TABLE = 'sdk';

    /**
     * @return mixed
     * @throws Exception\DatabaseException
     */
    public function multi(): array
    {
        $prism = new SdkPrism($this->request());
        return DB::connect()->table(self::TABLE)
            ->where(function (Where $w) use ($prism) {
                $prism->getKey() && $w->equalTo('key', $prism->getKey());
            })
            ->orderBy('key', 'asc')
            ->multi();
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
        $data = ['value' => $this->input('value')];
        if ($data) {
            return DB::connect()->table(self::TABLE)
                ->where(fn(Where $w) => $w->equalTo('key', $this->input('key')))
                ->update($data);
        }
        return true;
    }

}