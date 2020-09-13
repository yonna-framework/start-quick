<?php

namespace Yonna\QuickStart\Scope;

use Yonna\QuickStart\Prism\UserLicensePrism;
use Yonna\Database\DB;
use Yonna\Database\Driver\Pdo\Where;
use Yonna\Throwable\Exception;
use Yonna\Validator\ArrayValidator;

/**
 * Class License
 * @package Yonna\QuickStart\Scope
 */
class UserLicense extends AbstractScope
{

    const TABLE = 'user_license';

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
        $prism = new UserLicensePrism($this->request());
        return DB::connect()->table(self::TABLE)
            ->where(function (Where $w) use ($prism) {
                $prism->getUserId() && $w->equalTo('user_id', $prism->getUserId());
                $prism->getLicenseId() && $w->equalTo('license_id', $prism->getLicenseId());
                $prism->getStartTime() && $w->greaterThanOrEqualTo('start_time', $prism->getStartTime());
                $prism->getEndTime() && $w->lessThanOrEqualTo('end_time', $prism->getEndTime());
            })
            ->multi();
    }

    /**
     * @return mixed
     * @throws Exception\DatabaseException
     */
    public function count(): array
    {
        $prism = new UserLicensePrism($this->request());
        return DB::connect()->table(self::TABLE)
            ->where(function (Where $w) use ($prism) {
                $prism->getUserId() && $w->equalTo('user_id', $prism->getUserId());
                $prism->getLicenseId() && $w->equalTo('license_id', $prism->getLicenseId());
                $prism->getStartTime() && $w->greaterThanOrEqualTo('start_time', $prism->getStartTime());
                $prism->getEndTime() && $w->lessThanOrEqualTo('end_time', $prism->getEndTime());
            })
            ->count();
    }

    /**
     * @return int
     * @throws Exception\DatabaseException
     */
    public function delete()
    {
        ArrayValidator::anyone($this->input(), ['user_id', 'license_id'], function ($error) {
            Exception::throw($error);
        });
        $prism = new UserLicensePrism($this->request());
        return DB::connect()->table(self::TABLE)
            ->where(function (Where $w) use ($prism) {
                $prism->getUserId() && $w->equalTo('user_id', $prism->getUserId());
                $prism->getLicenseId() && $w->equalTo('license_id', $prism->getLicenseId());
            })
            ->delete();
    }

}