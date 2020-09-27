<?php

namespace Yonna\QuickStart\Scope;

use Yonna\Database\DB;
use Yonna\Database\Driver\Pdo\Where;
use Yonna\Foundation\Arr;
use Yonna\QuickStart\Mapping\League\LeagueTaskJoinerStatus;
use Yonna\QuickStart\Mapping\League\LeagueTaskStatus;
use Yonna\QuickStart\Mapping\User\AccountType;
use Yonna\QuickStart\Prism\LeagueTaskJoinerPrism;
use Yonna\Throwable\Exception;
use Yonna\Validator\ArrayValidator;

/**
 * Class LeagueTaskJoiner
 * @package Yonna\QuickStart\Scope
 */
class LeagueTaskJoiner extends AbstractScope
{

    const TABLE = 'league_task_joiner';

    /**
     * @return bool|mixed|null
     * @throws Exception\DatabaseException
     * @throws Exception\ErrorException
     * @throws \Throwable
     */
    public function insert()
    {
        ArrayValidator::required($this->input(), ['task_id', 'league_id'], function ($error) {
            Exception::throw($error);
        });
        $prism = new LeagueTaskJoinerPrism($this->request());
        $prism->setUserId($this->request()->getLoggingId());
        $one = DB::connect()->table(self::TABLE)
            ->where(fn(Where $w) => $w
                ->equalTo('task_id', $prism->getTaskId())
                ->equalTo('user_id', $prism->getUserId())
                ->in('status', [LeagueTaskJoinerStatus::DOING, LeagueTaskJoinerStatus::COMPLETE])
            )->one();
        if ($one) {
            return true;
        }
        $one = DB::connect()->table('league_member')
            ->where(fn(Where $w) => $w
                ->equalTo('user_id', $prism->getUserId())
                ->equalTo('status', LeagueTaskStatus::APPROVED)
            )
            ->one();
        if (!$one) {
            Exception::error('Please join the league first');
        }
        $add = [
            'task_id' => $prism->getTaskId(),
            'user_id' => $prism->getUserId(),
            'league_id' => $prism->getLeagueId(),
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
     * @return array
     * @throws Exception\DatabaseException
     */
    public function attach(): array
    {
        $prism = new LeagueTaskJoinerPrism($this->request());
        $data = $prism->getAttach();
        $isPage = isset($data['page']);
        $isOne = Arr::isAssoc($data);
        if ($isPage) {
            $tmp = $data['list'];
        } elseif ($isOne) {
            $tmp = [$data];
        } else {
            $tmp = $data;
        }
        if (!$tmp) {
            return [];
        }
        $ids = array_column($tmp, 'league_task_id');
        $values = DB::connect()->table(self::TABLE)
            ->where(fn(Where $w) => $w->in('task_id', $ids)->equalTo('status', LeagueTaskStatus::APPROVED))
            ->multi();

        $uids = array_column($values, 'league_task_joiner_user_id');
        if ($uids) {
            $avatars = DB::connect()->table('user_meta')
                ->where(fn(Where $w) => $w->in('user_id', $uids)->equalTo('key', 'avatar'))
                ->multi();
            $avatars = array_column($avatars, 'user_meta_value', 'user_meta_user_id');
        }

        $joiners = [];
        foreach ($values as $v) {
            if (!isset($joiners[$v['league_task_joiner_task_id']])) {
                $joiners[$v['league_task_joiner_task_id']] = [];
            }
            if ($v['league_task_joiner_user_id']) {
                if (!empty($avatars[$v['league_task_joiner_user_id']])) {
                    $v['league_task_joiner_avatar'] = $avatars[$v['league_task_joiner_user_id']][0];
                } else {
                    $v['league_task_joiner_avatar'] = null;
                }
            }
            $joiners[$v['league_task_joiner_task_id']][] = $v;
        }
        unset($values);
        foreach ($tmp as $uk => $u) {
            $tmp[$uk]['league_task_joiner'] = empty($joiners[$u['league_task_id']]) ? [] : $joiners[$u['league_task_id']];
        }
        if ($isPage) {
            $data['list'] = $tmp;
            return $data;
        }
        return $isOne ? $tmp[0] : $tmp;
    }

}