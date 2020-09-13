<?php

namespace Yonna\QuickStart\Scope;

use Yonna\Foundation\Arr;
use Yonna\QuickStart\Mapping\Data\DataStatus;
use Yonna\Database\DB;
use Yonna\Database\Driver\Pdo\Where;
use Yonna\QuickStart\Prism\LicensePrism;
use Yonna\Throwable\Exception;
use Yonna\Validator\ArrayValidator;

/**
 * Class License
 * @package Yonna\QuickStart\Scope
 */
class License extends AbstractScope
{

    const TABLE = 'license';

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
    public function tree(): array
    {
        $res = DB::connect()->table(self::TABLE)->orderBy('upper_id', 'asc')->multi();
        return Arr::tree($res, 0, 'license_id', 'license_upper_id');
    }

    /**
     * @return int
     * @throws Exception\DatabaseException
     */
    public function insert()
    {
        ArrayValidator::required($this->input(), ['name'], function ($error) {
            Exception::throw($error);
        });
        $add = [
            'name' => $this->input('name'),
            'status' => $this->input('status') ?? DataStatus::DISABLED,
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
        ArrayValidator::required($this->input(), ['id'], function ($error) {
            Exception::throw($error);
        });
        $data = [
            'name' => $this->input('name'),
            'status' => $this->input('status'),
            'sort' => $this->input('sort'),
        ];
        if ($data) {
            return DB::connect()->table(self::TABLE)
                ->where(fn(Where $w) => $w->equalTo('id', $this->input('id')))
                ->update($data);
        }
        return true;
    }

    /**
     * @return int
     * @throws \Throwable
     */
    public function delete()
    {
        ArrayValidator::required($this->input(), ['id'], function ($error) {
            Exception::throw($error);
        });
        $prism = new LicensePrism($this->request());
        return DB::transTrace(function () use ($prism) {
            $count = $this->scope(UserLicense::class, 'count', ['license_id' => $prism->getId()]);
            if ($count > 0) {
                if ($prism->isForce() !== true) {
                    Exception::throw('In use and cannot be deleted');
                }
                $this->scope(UserLicense::class, 'delete', ['license_id' => $prism->getId()]);
            }
            return DB::connect()->table(self::TABLE)
                ->where(fn(Where $w) => $w->equalTo('id', $prism->getId()))
                ->delete();
        });
    }

}