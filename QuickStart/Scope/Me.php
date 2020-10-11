<?php

namespace Yonna\QuickStart\Scope;

use Yonna\Database\DB;
use Yonna\Database\Driver\Pdo\Where;
use Yonna\QuickStart\Helper\Password;
use Yonna\QuickStart\Mapping\League\LeagueMemberPermission;
use Yonna\QuickStart\Mapping\League\LeagueMemberStatus;
use Yonna\QuickStart\Mapping\League\LeagueStatus;
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

    /**
     * @return mixed
     * @throws Exception\ThrowException
     */
    public function update()
    {
        $data = $this->input();
        $data['id'] = $this->request()->getLoggingId();
        return $this->scope(User::class, 'update', $data);
    }

    /**
     * 获取能加入的联盟
     * @return mixed
     * @throws Exception\ThrowException
     */
    public function leagueCanJoin()
    {
        $lm = $this->scope(LeagueMember::class, 'multi', [
            'user_id' => $this->request()->getLoggingId(),
            'status' => LeagueMemberStatus::APPROVED,
        ]);
        $lids = array_column($lm, 'league_member_league_id');
        return $this->scope(League::class, 'multi', [
            'not_ids' => $lids,
        ]);
    }

    /**
     * 申请加入社团
     * @return mixed
     * @throws Exception\ThrowException
     */
    public function leagueApply()
    {
        ArrayValidator::required($this->input(), ['league_id'], function ($error) {
            Exception::throw($error);
        });
        return $this->scope(LeagueMember::class, 'insert', [
            'user_id' => $this->request()->getLoggingId(),
            'league_id' => $this->input('league_id'),
            'permission' => LeagueMemberPermission::JOINER,
        ]);
    }

    /**
     * 放弃加入社团
     * @return mixed
     * @throws Exception\DatabaseException
     * @throws Exception\ThrowException
     */
    public function leagueGiveUpApply()
    {
        ArrayValidator::required($this->input(), ['league_id'], function ($error) {
            Exception::throw($error);
        });
        $res = DB::connect()->table('league_member')
            ->field('id')
            ->where(fn(Where $w) => $w
                ->equalTo('user_id', $this->request()->getLoggingId())
                ->equalTo('league_id', $this->input('league_id'))
                ->in('status', [LeagueMemberStatus::REJECTION, LeagueMemberStatus::PENDING])
            )->multi();
        if ($res) {
            $ids = array_column($res, 'league_member_id');
            return $this->scope(LeagueMember::class, 'multiStatus', [
                'ids' => $ids,
                'status' => LeagueMemberStatus::DELETE,
                'reason' => $this->input('reason'),
            ]);
        }
        return true;
    }

    /**
     * 离开社团
     * @return mixed
     * @throws Exception\DatabaseException
     * @throws Exception\ThrowException
     */
    public function leagueLeave()
    {
        ArrayValidator::required($this->input(), ['league_id'], function ($error) {
            Exception::throw($error);
        });
        $res = DB::connect()->table('league_member')
            ->field('id')
            ->where(fn(Where $w) => $w
                ->equalTo('user_id', $this->request()->getLoggingId())
                ->equalTo('league_id', $this->input('league_id'))
                ->equalTo('status', LeagueMemberStatus::APPROVED)
            )->multi();
        if ($res) {
            $ids = array_column($res, 'league_member_id');
            return $this->scope(LeagueMember::class, 'multiStatus', [
                'ids' => $ids,
                'status' => LeagueMemberStatus::DELETE,
                'reason' => $this->input('reason'),
            ]);
        }
        return true;
    }

    /**
     * 获取任务详情(包含个人信息)
     * @return mixed
     * @throws Exception\ThrowException
     */
    public function taskInfo()
    {
        ArrayValidator::required($this->input(), ['id'], function ($error) {
            Exception::throw($error);
        });
        $info = $this->scope(LeagueTask::class, 'one', ['id' => $this->input('id')]);
        $lmaCount = 0;
        $lmjCount = 0;
        if ($this->input('league_id')) {
            $lmaCount = DB::connect()->table('league_member')
                ->where(fn(Where $w) => $w
                    ->equalTo('user_id', $this->request()->getLoggingId())
                    ->equalTo('status', LeagueMemberStatus::APPROVED)
                    ->in('permission', [LeagueMemberPermission::OWNER, LeagueMemberPermission::MANAGER])
                    ->equalTo('league_id', $this->input('league_id'))
                )
                ->count('id');
            $lmjCount = DB::connect()->table('league_member')
                ->where(fn(Where $w) => $w
                    ->equalTo('user_id', $this->request()->getLoggingId())
                    ->equalTo('status', LeagueMemberStatus::APPROVED)
                    ->equalTo('permission', LeagueMemberPermission::JOINER)
                    ->equalTo('league_id', $this->input('league_id'))
                )
                ->count('id');
        }
        $ltaCount = DB::connect()->table('league_task_assign')
            ->where(fn(Where $w) => $w
                ->equalTo('user_id', $this->request()->getLoggingId())
                ->equalTo('task_id', $this->input('id'))
            )
            ->count('id');
        $ltjCount = DB::connect()->table('league_task_joiner')
            ->where(fn(Where $w) => $w
                ->equalTo('user_id', $this->request()->getLoggingId())
                ->equalTo('task_id', $this->input('id'))
            )
            ->count('id');
        $info['is_allow_assign'] = false;
        $info['is_allow_join'] = false;
        $info['is_assigning'] = false;
        $info['is_joining'] = false;
        if ($info['league_task_status'] === LeagueTaskStatus::APPROVED) {
            $info['is_allow_assign'] = $lmaCount > 0 && $ltaCount <= 0;
            $info['is_allow_join'] = $lmjCount > 0 && $ltjCount <= 0;
            $info['is_assigning'] = $ltaCount > 0;
            $info['is_joining'] = $ltjCount > 0;
        }
        return $info;
    }

    /**
     * 我管理的社团能接的任务
     * @return array[]
     * @throws Exception\DatabaseException
     * @throws Exception\ThrowException
     */
    public function taskCanAssign()
    {
        $return = [
            'task' => [],
            'league' => [],
        ];
        // 获取我管理的社团
        $my = DB::connect()->table('league_member')
            ->field('league_id')
            ->where(fn(Where $w) => $w
                ->equalTo('user_id', $this->request()->getLoggingId())
                ->equalTo('status', LeagueMemberStatus::APPROVED)
                ->in('permission', [LeagueMemberPermission::OWNER, LeagueMemberPermission::MANAGER])
            )
            ->multi();
        if (!$my) {
            return $return;
        }
        $leagueIds = array_column($my, 'league_member_league_id');
        $leagueIds = array_unique($leagueIds);
        $leagueIds = array_values($leagueIds);
        $return['league'] = $this->scope(League::class, 'multi', ['ids' => $leagueIds, 'status' => LeagueStatus::APPROVED]);
        // 排除已接任务
        $assignedTask = DB::connect()->table('league_task_assign')
            ->where(fn(Where $w) => $w->in('league_id', $leagueIds))
            ->multi();
        $assignedTaskIds = array_column($assignedTask, 'league_task_assign_task_id');
        $assignedTaskIds = array_unique($assignedTaskIds);
        $assignedTaskIds = array_values($assignedTaskIds);
        $return['task'] = $this->scope(LeagueTask::class, 'multi', [
            'status' => LeagueTaskStatus::APPROVED,
            'not_ids' => $assignedTaskIds,
        ]);
        return $return;
    }

    /**
     * 获取可以加入的任务
     * @return array|mixed
     * @throws Exception\DatabaseException
     * @throws Exception\ThrowException
     */
    public function taskCanJoin()
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
            $taskIds = array_column($assign, 'league_task_assign_task_id');
            $taskIds = array_unique($taskIds);
            sort($taskIds);
            //TODO 排除已加入的任务
            $tasks = $this->scope(LeagueTask::class, 'multi', ['ids' => $taskIds, 'attach_joiner' => true]);
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

    /**
     * 帮社团接受任务
     * @return int
     * @throws Exception\DatabaseException
     * @throws Exception\ErrorException
     * @throws Exception\ThrowException
     */
    public function taskAssign()
    {
        $one = DB::connect()->table('league_member')
            ->where(fn(Where $w) => $w
                ->equalTo('league_id', $this->input('league_id'))
                ->equalTo('user_id', $this->request()->getLoggingId())
                ->equalTo('status', LeagueMemberStatus::APPROVED)
                ->in('permission', [LeagueMemberPermission::OWNER, LeagueMemberPermission::MANAGER])
            )
            ->one();
        if (!$one) {
            Exception::error('no permission');
        }
        return $this->scope(LeagueTaskAssign::class, 'insert', [
            'task_id' => $this->input('task_id'),
            'league_id' => $this->input('league_id'),
        ]);
    }

    /**
     * 帮社团放弃任务
     * @return int
     * @throws Exception\DatabaseException
     * @throws Exception\ErrorException
     * @throws Exception\ThrowException
     */
    public function taskUnAssign()
    {
        ArrayValidator::required($this->input(), ['task_id', 'league_id'], function ($error) {
            Exception::throw($error);
        });
        $one = DB::connect()->table('league_member')
            ->where(fn(Where $w) => $w
                ->equalTo('league_id', $this->input('league_id'))
                ->equalTo('user_id', $this->request()->getLoggingId())
                ->equalTo('status', LeagueMemberStatus::APPROVED)
                ->in('permission', [LeagueMemberPermission::OWNER, LeagueMemberPermission::MANAGER])
            )
            ->one();
        if (!$one) {
            Exception::error('no permission');
        }
        return DB::connect()->table('league_task_assign')
            ->where(fn(Where $w) => $w
                ->equalTo('task_id', $this->input('task_id'))
                ->equalTo('league_id', $this->input('league_id'))
            )
            ->delete();
    }

    /**
     * 加入任务
     * @return int
     * @throws Exception\DatabaseException
     * @throws Exception\ParamsException
     */
    public function taskJoin()
    {
        ArrayValidator::required($this->input(), ['task_id', 'league_id'], function ($error) {
            Exception::throw($error);
        });
        $one = DB::connect()->table('league_task_joiner')
            ->where(fn(Where $e) => $e
                ->equalTo('user_id', $this->request()->getLoggingId())
                ->equalTo('task_id', $this->input('task_id'))
            )
            ->one();
        if ($one) {
            Exception::params('You have already applied, please wait for the review result . ');
        }
        $data = [
            'user_id' => $this->request()->getLoggingId(),
            'task_id' => $this->input('task_id'),
            'league_id' => $this->input('league_id'),
        ];
        return DB::connect()->table('league_task_joiner')->insert($data);
    }

    /**
     * 退出任务
     * @return false|int
     * @throws Exception\DatabaseException
     */
    public function taskUnJoin()
    {
        ArrayValidator::required($this->input(), ['task_id'], function ($error) {
            Exception::throw($error);
        });
        return DB::connect()->table('league_task_joiner')
            ->where(fn(Where $e) => $e
                ->equalTo('user_id', $this->request()->getLoggingId())
                ->equalTo('task_id', $this->input('task_id'))
            )
            ->delete();
    }

    /**
     * 我发布的任务
     * @return mixed
     * @throws Exception\ThrowException
     */
    public function taskPublishList()
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

    /**
     * 我参加的任务
     * @return array|mixed
     * @throws Exception\DatabaseException
     * @throws Exception\ThrowException
     */
    public function taskJoinLIst()
    {
        $join = DB::connect()->table('league_task_joiner')
            ->where(fn(Where $w) => $w->equalTo('user_id', $this->request()->getLoggingId()))
            ->multi();
        if ($join) {
            $taskIds = array_column($join, 'league_task_joiner_task_id');
            $taskIds = array_unique($taskIds);
            sort($taskIds);
            $tasks = $this->scope(LeagueTask::class, 'multi', ['ids' => $taskIds, 'attach_joiner' => true]);
            $tid = array_column($tasks, 'league_task_id');
            $tmap = array_combine($tid, $tasks);
            foreach ($join as $k => $v) {
                $join[$k]['league_task_info'] = $tmap[$v['league_task_joiner_task_id']];
            }
            return $join;
        }
        return [];
    }

}