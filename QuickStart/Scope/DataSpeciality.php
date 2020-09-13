<?php

namespace Yonna\QuickStart\Scope;

use Yonna\QuickStart\Mapping\Data\DataStatus;
use Yonna\QuickStart\Prism\DataPrism;
use Yonna\Database\DB;
use Yonna\Database\Driver\Pdo\Where;
use Yonna\Throwable\Exception;
use Yonna\Validator\ArrayValidator;

/**
 * Class Speciality
 * @package Yonna\QuickStart\Scope
 */
class DataSpeciality extends AbstractScope
{

    const TABLE = 'data_speciality';

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
        $prism = new DataPrism($this->request());
        return DB::connect()->table(self::TABLE)
            ->where(function (Where $w) use ($prism) {
                $prism->getId() && $w->equalTo('id', $prism->getId());
                $prism->getStatus() && $w->equalTo('status', $prism->getStatus());
                $prism->getName() && $w->like('name', '%' . $prism->getName() . '%');
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
        $prism = new DataPrism($this->request());
        return DB::connect()->table(self::TABLE)
            ->where(function (Where $w) use ($prism) {
                $prism->getId() && $w->equalTo('id', $prism->getId());
                $prism->getStatus() && $w->equalTo('status', $prism->getStatus());
                $prism->getName() && $w->like('name', '%' . $prism->getName() . '%');
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
     * @throws Exception\DatabaseException
     */
    public function delete()
    {
        ArrayValidator::required($this->input(), ['id'], function ($error) {
            Exception::throw($error);
        });
        return DB::connect()->table(self::TABLE)
            ->where(fn(Where $w) => $w->equalTo('id', $this->input('id')))
            ->delete();
    }

    /**
     * @return int
     * @throws Exception\DatabaseException
     */
    public function multiDelete()
    {
        ArrayValidator::required($this->input(), ['ids'], function ($error) {
            Exception::throw($error);
        });
        return DB::connect()->table(self::TABLE)
            ->where(fn(Where $w) => $w->in('id', $this->input('ids')))
            ->delete();
    }

    /**
     * @return int
     * @throws Exception\DatabaseException
     */
    public function multiStatus()
    {
        ArrayValidator::required($this->input(), ['ids', 'status'], function ($error) {
            Exception::throw($error);
        });
        return DB::connect()->table(self::TABLE)
            ->where(fn(Where $w) => $w->in('id', $this->input('ids')))
            ->update(["status" => $this->input('status')]);
    }

}