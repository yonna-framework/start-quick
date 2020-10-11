<?php

namespace Yonna\QuickStart\Scope;

use Throwable;
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
        $prism = new LeagueTaskPrism($this->request());
        $result = DB::connect()->table(self::TABLE)
            ->where(fn(Where $w) => $w->equalTo('id', $prism->getId()))
            ->one();
        if ($prism->isAttachJoiner()) {
            $result = $this->scope(LeagueTaskJoiner::class, 'attach', ['attach' => $result]);
        }
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
                $prism->getIds() && $w->in('id', $prism->getIds());
                $prism->getNotIds() && $w->notIn('id', $prism->getNotIds());
                $prism->getLeagueId() && $w->equalTo('league_id', $prism->getLeagueId());
                $prism->getLeagueIds() && $w->in('league_id', $prism->getLeagueIds());
                $prism->getUserId() && $w->equalTo('user_id', $prism->getUserId());
                $prism->getName() && $w->like('name', '%' . $prism->getName() . '%');
                $prism->getStatus() && $w->equalTo('status', $prism->getStatus());
                $prism->getStatuss() && $w->in('status', $prism->getStatuss());
            })
            ->orderBy('sort', 'desc')
            ->orderBy('id', 'desc')
            ->multi();
        if ($prism->isAttachJoiner()) {
            $result = $this->scope(LeagueTaskJoiner::class, 'attach', ['attach' => $result]);
        }
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
                $prism->getLeagueIds() && $w->in('league_id', $prism->getLeagueIds());
                $prism->getUserId() && $w->equalTo('user_id', $prism->getUserId());
                $prism->getName() && $w->like('name', '%' . $prism->getName() . '%');
                $prism->getStatus() && $w->equalTo('status', $prism->getStatus());
                $prism->getStatuss() && $w->in('status', $prism->getStatuss());
            })
            ->orderBy('sort', 'desc')
            ->orderBy('id', 'desc')
            ->page($prism->getCurrent(), $prism->getPer());
        if ($prism->isAttachJoiner()) {
            $result = $this->scope(LeagueTaskJoiner::class, 'attach', ['attach' => $result]);
        }
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
            'name', 'introduction',
            'people_number', 'points',
            'start_time', 'end_time'
        ], function ($error) {
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
            'current_number' => 0,
            'people_number' => $prism->getPeopleNumber(),
            'start_time' => $prism->getStartTime(),
            'end_time' => $prism->getEndTime(),
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
        switch ($prism->getStatus()) {
            case LeagueTaskStatus::REJECTION:
                $data['rejection_time'] = time();
                $data['rejection_reason'] = $prism->getReason();
                break;
            case LeagueTaskStatus::APPROVED:
                $data['pass_time'] = time();
                $data['pass_reason'] = $prism->getReason();
                break;
            case LeagueTaskStatus::DELETE:
                $data['delete_time'] = time();
                $data['delete_reason'] = $prism->getReason();
                break;
        }
        return DB::connect()->table(self::TABLE)
            ->where(fn(Where $w) => $w->equalTo('id', $prism->getId()))
            ->update($data);
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
        $status = $this->input('status');
        $reason = $this->input('reason');
        $data = ['status' => $status];
        switch ($status) {
            case LeagueTaskStatus::REJECTION:
                $data['rejection_time'] = time();
                $data['rejection_reason'] = $reason;
                break;
            case LeagueTaskStatus::APPROVED:
                $data['pass_time'] = time();
                $data['pass_reason'] = $reason;
                break;
            case LeagueTaskStatus::DELETE:
                $data['delete_time'] = time();
                $data['delete_reason'] = $reason;
                break;
        }
        return DB::connect()->table(self::TABLE)
            ->where(fn(Where $w) => $w->in('id', $this->input('ids')))
            ->update($data);
    }

    /**
     * @return mixed
     * @throws Exception\ThrowException
     */
    public function delete()
    {
        return $this->scope(LeagueTask::class, 'multiStatus', [
            'ids' => [$this->input('id')],
            'status' => LeagueTaskStatus::DELETE,
        ]);
    }

}