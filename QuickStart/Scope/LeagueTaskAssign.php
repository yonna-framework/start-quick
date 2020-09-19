<?php

namespace Yonna\QuickStart\Scope;

use Throwable;
use Yonna\QuickStart\Mapping\League\LeagueStatus;
use Yonna\QuickStart\Mapping\League\LeagueTaskStatus;
use Yonna\QuickStart\Prism\LeagueTaskAssignPrism;
use Yonna\QuickStart\Prism\LeagueTaskPrism;
use Yonna\Database\DB;
use Yonna\Database\Driver\Pdo\Where;
use Yonna\Throwable\Exception;
use Yonna\Validator\ArrayValidator;

/**
 * Class LeagueTaskAssign
 * @package Yonna\QuickStart\Scope
 */
class LeagueTaskAssign extends AbstractScope
{

    const TABLE = 'league_task_assign';

    /**
     * @return int
     * @throws Throwable
     */
    public function insert()
    {
        ArrayValidator::required($this->input(), ['task_id', 'league_id'], function ($error) {
            Exception::throw($error);
        });
        $prism = new LeagueTaskAssignPrism($this->request());
        $add = [
            'task_id' => $prism->getTaskId(),
            'league_id' => $prism->getLeagueId(),
            'assign_time' => time(),
        ];
        return DB::connect()->table(self::TABLE)->insert($add);
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
        $prism = new LeagueTaskPrism($this->request());
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