<?php

namespace Yonna\QuickStart\Scope;

use Throwable;
use Yonna\QuickStart\Mapping\League\LeagueStatus;
use Yonna\QuickStart\Prism\LeaguePrism;
use Yonna\Database\DB;
use Yonna\Database\Driver\Pdo\Where;
use Yonna\Throwable\Exception;
use Yonna\Validator\ArrayValidator;

/**
 * Class League
 * @package Yonna\QuickStart\Scope
 */
class League extends AbstractScope
{

    const TABLE = 'league';

    /**
     * @return mixed
     * @throws Exception\DatabaseException
     * @throws Exception\ThrowException
     */
    public function one(): array
    {
        ArrayValidator::required($this->input(), ['id'], function ($error) {
            Exception::throw($error);
        });
        $result = DB::connect()->table(self::TABLE)
            ->where(fn(Where $w) => $w->equalTo('id', $this->input('id')))
            ->one();
        $result = $this->scope(LeagueAssociateHobby::class, 'attach', ['attach' => $result]);
        $result = $this->scope(LeagueAssociateWork::class, 'attach', ['attach' => $result]);
        $result = $this->scope(LeagueAssociateSpeciality::class, 'attach', ['attach' => $result]);
        return $result;
    }

    /**
     * @return mixed
     * @throws Exception\DatabaseException
     * @throws Exception\ThrowException
     */
    public function multi(): array
    {
        $prism = new LeaguePrism($this->request());
        $result = DB::connect()->table(self::TABLE)
            ->where(function (Where $w) use ($prism) {
                $prism->getId() && $w->equalTo('id', $prism->getId());
                $prism->getName() && $w->like('name', '%' . $prism->getName() . '%');
                $prism->getStatus() && $w->equalTo('status', $prism->getStatus());
            })
            ->orderBy('sort', 'desc')
            ->orderBy('id', 'desc')
            ->multi();
        $result = $this->scope(LeagueAssociateHobby::class, 'attach', ['attach' => $result]);
        $result = $this->scope(LeagueAssociateWork::class, 'attach', ['attach' => $result]);
        $result = $this->scope(LeagueAssociateSpeciality::class, 'attach', ['attach' => $result]);
        return $result;
    }

    /**
     * @return mixed
     * @throws Exception\DatabaseException
     * @throws Exception\ThrowException
     */
    public function page(): array
    {
        $prism = new LeaguePrism($this->request());
        $result = DB::connect()->table(self::TABLE)
            ->where(function (Where $w) use ($prism) {
                $prism->getId() && $w->equalTo('id', $prism->getId());
                $prism->getName() && $w->like('name', '%' . $prism->getName() . '%');
                $prism->getStatus() && $w->equalTo('status', $prism->getStatus());
            })
            ->orderBy('sort', 'desc')
            ->orderBy('id', 'desc')
            ->page($prism->getCurrent(), $prism->getPer());
        $result = $this->scope(LeagueAssociateHobby::class, 'attach', ['attach' => $result]);
        $result = $this->scope(LeagueAssociateWork::class, 'attach', ['attach' => $result]);
        $result = $this->scope(LeagueAssociateSpeciality::class, 'attach', ['attach' => $result]);
        return $result;
    }

    /**
     * @return int
     * @throws Exception\ParamsException
     * @throws Exception\ThrowException
     * @throws Throwable
     */
    public function insert()
    {
        ArrayValidator::required($this->input(), [
            'master_user_account',
            'name',
            'slogan',
            'introduction',
            'logo_pic',
            'business_license_pic',
        ], function ($error) {
            Exception::throw($error);
        });
        $prism = new LeaguePrism($this->request());
        $account = $this->scope(UserAccount::class, 'one', ['string' => $prism->getMasterUserAccount()]);
        if (empty($account['user_account_id'])) {
            Exception::params('Account is not exist');
        }
        $prism->setMasterUserId($account['user_account_id']);
        $introduction = $this->xoss_save($prism->getIntroduction() ?? '');
        $add = [
            'master_user_id' => $prism->getMasterUserId(),
            'name' => $prism->getName(),
            'slogan' => $prism->getSlogan(),
            'introduction' => $introduction,
            'logo_pic' => $prism->getLogoPic(),
            'business_license_pic' => $prism->getBusinessLicensePic(),
            'status' => $prism->getStatus() ?? LeagueStatus::PENDING,
            'apply_reason' => $prism->getApplyReason() ?? '',
            'apply_time' => time(),
            'rejection_time' => 0,
            'pass_time' => 0,
            'delete_time' => 0,
            'sort' => $prism->getSort() ?? 0,
        ];
        return DB::transTrace(function () use ($add, $prism) {
            $id = DB::connect()->table(self::TABLE)->insert($add);
            if ($prism->getHobby()) {
                $this->scope(LeagueAssociateHobby::class, 'cover', [
                    'league_id' => $id,
                    'data' => $prism->getHobby()
                ]);
            }
            if ($prism->getWork()) {
                $this->scope(LeagueAssociateWork::class, 'cover', [
                    'league_id' => $id,
                    'data' => $prism->getWork()
                ]);
            }
            if ($prism->getSpeciality()) {
                $this->scope(LeagueAssociateSpeciality::class, 'cover', [
                    'league_id' => $id,
                    'data' => $prism->getSpeciality()
                ]);
            }
            return $id;
        });
    }

    /**
     * @return int
     * @throws Throwable
     */
    public function update()
    {
        ArrayValidator::required($this->input(), ['id'], function ($error) {
            Exception::throw($error);
        });
        $prism = new LeaguePrism($this->request());
        if ($prism->getMasterUserAccount()) {
            $account = $this->scope(UserAccount::class, 'one', ['string' => $prism->getMasterUserAccount()]);
            if (empty($account['user_account_id'])) {
                Exception::params('Account is not exist');
            }
            $prism->setMasterUserId($account['user_account_id']);
        }
        if ($prism->getIntroduction()) {
            $prism->setIntroduction($this->xoss_save($prism->getIntroduction()));
        }

        $data = [
            'master_user_id' => $prism->getMasterUserId(),
            'name' => $prism->getName(),
            'slogan' => $prism->getSlogan(),
            'introduction' => $prism->getIntroduction(),
            'logo_pic' => $prism->getLogoPic(),
            'business_license_pic' => $prism->getBusinessLicensePic(),
            'status' => $prism->getStatus(),
            'sort' => $prism->getSort(),
        ];
        DB::transTrace(function () use ($data, $prism) {
            if ($data) {
                return DB::connect()->table(self::TABLE)
                    ->where(fn(Where $w) => $w->equalTo('id', $prism->getId()))
                    ->update($data);
            }
            if ($prism->getHobby()) {
                $this->scope(LeagueAssociateHobby::class, 'cover', [
                    'league_id' => $prism->getId(),
                    'data' => $prism->getHobby()
                ]);
            }
            if ($prism->getWork()) {
                $this->scope(LeagueAssociateWork::class, 'cover', [
                    'league_id' => $prism->getId(),
                    'data' => $prism->getWork()
                ]);
            }
            if ($prism->getSpeciality()) {
                $this->scope(LeagueAssociateSpeciality::class, 'cover', [
                    'league_id' => $prism->getId(),
                    'data' => $prism->getSpeciality()
                ]);
            }
            return true;
        });
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
            ->update(["status" => LeagueStatus::DELETE]);
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