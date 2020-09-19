<?php

namespace Yonna\QuickStart\Scope;

use Throwable;
use Yonna\QuickStart\Mapping\League\LeagueStatus;
use Yonna\QuickStart\Mapping\League\LeagueTaskStatus;
use Yonna\QuickStart\Prism\LeagueTaskPrism;
use Yonna\Database\DB;
use Yonna\Database\Driver\Pdo\Where;
use Yonna\Throwable\Exception;
use Yonna\Validator\ArrayValidator;

/**
 * Class LeagueTask
 * @package Yonna\QuickStart\Scope
 */
class LeagueTask extends AbstractScope
{

    const TABLE = 'league_task';

    /**
     * @return mixed
     * @throws Exception\DatabaseException
     */
    public function one(): array
    {
        ArrayValidator::required($this->input(), ['id'], function ($error) {
            Exception::throw($error);
        });
        $result = DB::connect()->table(self::TABLE)
            ->where(fn(Where $w) => $w->equalTo('id', $this->input('id')))
            ->one();
        return $result;
    }

    /**
     * @return mixed
     * @throws Exception\DatabaseException
     */
    public function multi(): array
    {
        $prism = new LeagueTaskPrism($this->request());
        $result = DB::connect()->table(self::TABLE)
            ->where(function (Where $w) use ($prism) {
                $prism->getId() && $w->equalTo('id', $prism->getId());
                $prism->getLeagueId() && $w->equalTo('league_id', $prism->getLeagueId());
                $prism->getName() && $w->like('name', '%' . $prism->getName() . '%');
                $prism->getStatus() && $w->equalTo('status', $prism->getStatus());
            })
            ->orderBy('sort', 'desc')
            ->orderBy('id', 'desc')
            ->multi();
        return $result;
    }

    /**
     * @return mixed
     * @throws Exception\DatabaseException
     */
    public function page(): array
    {
        $prism = new LeagueTaskPrism($this->request());
        $result = DB::connect()->table(self::TABLE)
            ->where(function (Where $w) use ($prism) {
                $prism->getId() && $w->equalTo('id', $prism->getId());
                $prism->getLeagueId() && $w->equalTo('league_id', $prism->getLeagueId());
                $prism->getName() && $w->like('name', '%' . $prism->getName() . '%');
                $prism->getStatus() && $w->equalTo('status', $prism->getStatus());
            })
            ->orderBy('sort', 'desc')
            ->orderBy('id', 'desc')
            ->page($prism->getCurrent(), $prism->getPer());
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
        ArrayValidator::required($this->input(), ['name', 'introduction', 'points'], function ($error) {
            Exception::throw($error);
        });
        $prism = new LeagueTaskPrism($this->request());
        if ($prism->getLeagueId()) {
            $league = $this->scope(League::class, 'one', ['id' => $prism->getLeagueId()]);
            if (!$league) {
                Exception::params('League is not exist');
            }
        }
        $prism->setIntroduction($this->xoss_save($prism->getIntroduction()));
        $add = [
            'name' => $prism->getName(),
            'user_id' => $this->request()->getLoggingId(),
            'league_id' => $prism->getLeagueId(),
            'points' => round($prism->getPoints(), 1),
            'introduction' => $prism->getIntroduction(),
            'status' => $prism->getStatus() ?? LeagueTaskStatus::PENDING,
            'apply_reason' => $prism->getApplyReason() ?? '',
            'apply_time' => time(),
            'rejection_time' => 0,
            'pass_time' => 0,
            'delete_time' => 0,
            'event_photos' => [],
            'self_evaluation' => 0,
            'platform_evaluation' => 0,
            'sort' => $prism->getSort() ?? 0,
        ];
        return DB::transTrace(function () use ($add, $prism) {
            $id = DB::connect()->table(self::TABLE)->insert($add);
            if ($prism->getLeagueId()) {
                // 发起人的社团自动参与
                $this->scope(LeagueTaskAssign::class, 'insert', [
                    'task_id' => $id,
                    'league_id' => $prism->getLeagueId(),
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
        $prism = new LeagueTaskPrism($this->request());
        if ($prism->getIntroduction()) {
            $prism->setIntroduction($this->xoss_save($prism->getIntroduction()));
        }

        $data = [
            'name' => $prism->getName(),
            'introduction' => $prism->getIntroduction(),
            'points' => $prism->getPoints() ? round($prism->getPoints(), 1) : null,
            'status' => $prism->getStatus(),
            'sort' => $prism->getSort(),
            'event_photos' => $prism->getEventPhotos(),
            'self_evaluation' => $prism->getSelfEvaluation() ? round($prism->getSelfEvaluation(), 1) : null,
            'platform_evaluation' => $prism->getPlatformEvaluation() ? round($prism->getPlatformEvaluation(), 1) : null,
        ];
        return DB::connect()->table(self::TABLE)
            ->where(fn(Where $w) => $w->equalTo('id', $prism->getId()))
            ->update($data);
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
        $one = DB::connect()->table(self::TABLE)
            ->where(fn(Where $w) => $w->equalTo('id', $this->input('id')))
            ->one();
        if ($one !== LeagueTaskStatus::PENDING && $one !== LeagueTaskStatus::REJECTION) {
            Exception::params('status error');
        }
        return DB::connect()->table(self::TABLE)
            ->where(fn(Where $w) => $w->equalTo('id', $this->input('id')))
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