<?php

namespace Yonna\QuickStart\Scope;

use Yonna\Database\DB;
use Yonna\Database\Driver\Pdo\Where;
use Yonna\QuickStart\Helper\Password;
use Yonna\QuickStart\Mapping\League\LeagueMemberStatus;
use Yonna\QuickStart\Mapping\League\LeagueTaskJoinerStatus;
use Yonna\QuickStart\Mapping\League\LeagueTaskStatus;
use Yonna\Throwable\Exception;
use Yonna\Validator\ArrayValidator;

class Me extends AbstractScope
{

    /**
     * 获取当前登录用户详情
     * @return array
     * @throws Exception\ThrowException
     */
    public function one(): array
    {
        return $this->scope(User::class, 'one', ['id' => $this->request()->getLoggingId()]);
    }

    /**
     * @return int
     * @throws Exception\DatabaseException
     * @throws Exception\ParamsException
     */
    public function password()
    {
        $pwd = $this->input('password');
        if ($pwd) {
            if (!Password::check($pwd)) {
                Exception::params(Password::getFalseMsg());
            }
            $pwd = Password::parse($pwd);
        }
        $data = ['password' => $pwd];
        if ($data) {
            return DB::connect()->table('user')
                ->where(fn(Where $w) => $w->equalTo('id', $this->request()->getLoggingId()))
                ->update($data);
        }
        return true;
    }

    public function task()
    {
        return $this->scope(LeagueTask::class, 'multi', [
            'user_id' => $this->request()->getLoggingId(),
            'statuss' => [
                LeagueTaskStatus::REJECTION,
                LeagueTaskStatus::PENDING,
                LeagueTaskStatus::APPROVED,
                LeagueTaskStatus::COMPLETE,
            ],
        ]);
    }

    public function taskAssign()
    {
        // 查我加入的联盟
        $member = DB::connect()->table('league_member')
            ->where(fn(Where $w) => $w
                ->equalTo('status', LeagueMemberStatus::APPROVED)
                ->equalTo('user_id', $this->request()->getLoggingId())
            )->multi();
        if ($member) {
            $league_ids = array_column($member, 'league_member_league_id');
            $league_ids = array_unique($league_ids);
            sort($league_ids);
        } else {
            return [];
        }
        // 查联盟分到的任务
        $assign = DB::connect()->table('league_task_assign')
            ->where(fn(Where $w) => $w->in('league_id', $league_ids))
            ->multi();
        if ($assign) {
            $task_ids = array_column($assign, 'league_task_assign_task_id');
            $task_ids = array_unique($task_ids);
            sort($task_ids);
            $tasks = $this->scope(LeagueTask::class, 'multi', ['ids' => $task_ids, 'attach_joiner' => true]);
            $tid = array_column($tasks, 'league_task_id');
            $tmap = array_combine($tid, $tasks);
            foreach ($assign as $k => $v) {
                $assign[$k]['league_task_info'] = $tmap[$v['league_task_assign_task_id']];
            }
            return $assign;
        } else {
            return [];
        }
    }

    public function taskJoin()
    {
        $list = DB::connect()->table('league_task_joiner')
            ->where(fn(Where $w) => $w
                ->equalTo('user_id', $this->request()->getLoggingId())
            )
            ->multi();
        return [];
    }

    public function taskApply()
    {
        ArrayValidator::required($this->input(), ['task_id', 'league_id'], function ($error) {
            Exception::throw($error);
        });
        $one = DB::connect()->table('league_task_joiner')
            ->where(fn(Where $e) => $e
                ->equalTo('user_id', $this->request()->getLoggingId())
                ->equalTo('task_id', $this->input('task_id'))
                ->in('status', [
                    LeagueTaskJoinerStatus::PENDING,
                    LeagueTaskJoinerStatus::APPROVED,
                    LeagueTaskJoinerStatus::COMPLETE,
                ])
            )
            ->one();
        if ($one) {
            Exception::params('You have already applied, please wait for the review result.');
        }
        $data = [
            'user_id' => $this->request()->getLoggingId(),
            'task_id' => $this->input('task_id'),
            'league_id' => $this->input('league_id'),
            'status' => LeagueTaskJoinerStatus::PENDING,
        ];
        return DB::connect()->table('league_task_joiner')->insert($data);
    }

    public function taskGiveUp()
    {
        ArrayValidator::required($this->input(), ['task_id'], function ($error) {
            Exception::throw($error);
        });
        return DB::connect()->table('league_task_joiner')
            ->where(fn(Where $e) => $e
                ->equalTo('user_id', $this->request()->getLoggingId())
                ->equalTo('task_id', $this->input('task_id'))
            )
            ->update(['status' => LeagueTaskJoinerStatus::GIVE_UP]);
    }

}