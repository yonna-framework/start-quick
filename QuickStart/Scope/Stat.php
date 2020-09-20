<?php

namespace Yonna\QuickStart\Scope;

use Yonna\Database\DB;
use Yonna\Database\Driver\Pdo\Where;
use Yonna\QuickStart\Mapping\League\LeagueStatus;
use Yonna\QuickStart\Mapping\League\LeagueTaskStatus;
use Yonna\QuickStart\Mapping\User\AccountType;
use Yonna\QuickStart\Mapping\User\UserStatus;
use Yonna\Throwable\Exception\DatabaseException;

class Stat extends AbstractScope
{


    /**
     * @return array
     * @throws DatabaseException
     */
    public function user(): array
    {
        $stat = [];
        foreach (UserStatus::toKv('label') as $k => $v) {
            $stat[$k] = [
                'key' => $k,
                'value' => 0,
                'label' => $v,
            ];
        }
        $userCount = DB::connect()
            ->table('user')
            ->field('count(`id`) as qty,status')
            ->groupBy('status')
            ->multi();
        foreach ($userCount as $u) {
            $stat[$u['user_status']]['value'] = $u['qty'];
        }
        return array_values($stat);
    }

    /**
     * @return array
     * @throws DatabaseException
     */
    public function account(): array
    {
        $stat = [];
        foreach (AccountType::toKv('label') as $k => $v) {
            $stat[$k] = [
                'key' => $k,
                'value' => 0,
                'label' => $v,
            ];
        }
        $userCount = DB::connect()
            ->table('user_account')
            ->field('count(`user_id`) as qty,type')
            ->groupBy('type')
            ->multi();
        foreach ($userCount as $u) {
            $stat[$u['user_account_type']]['value'] = $u['qty'];
        }
        return array_values($stat);
    }

    /**
     * @return array
     * @throws DatabaseException
     */
    public function task(): array
    {
        $color = [
            LeagueTaskStatus::PENDING => '#13C2C2',
            LeagueTaskStatus::REJECTION => '#FACC14',
            LeagueTaskStatus::APPROVED => '#1890ff',
            LeagueTaskStatus::DELETE => '#E04240',
            LeagueTaskStatus::COMPLETE => '#2FC25B',
        ];
        $stat = [];
        foreach (LeagueTaskStatus::toKv('label') as $k => $v) {
            $stat[$k] = [
                'name' => $v,
                'percent' => 0,
                'color' => $color[$k],
            ];
        }
        $total = DB::connect()->table('league_task')->count('id');
        $res = DB::connect()->table('league_task')->field('count(`id`) as qty,status')->groupBy('status')->multi();
        foreach ($res as $v) {
            $stat[$v['league_task_status']]['percent'] = round($v['qty'] / $total * 100);
        }
        return array_values($stat);
    }

    /**
     * @return array
     * @throws DatabaseException
     */
    public function userGrow(): array
    {
        $res = DB::connect()
            ->table('user')
            ->field('id,register_time')
            ->where(fn(Where $w) => $w->notEqualTo('status', UserStatus::DELETE))
            ->multi();
        $tmp = [];
        for ($i = 5; $i >= 0; $i--) {
            $d = date('Y-m-d H:i:s', strtotime(date('Y-m-01 23:59:59') . " -1day +1month -{$i}month"));
            $time = strtotime($d);
            $txt = date('Y年m月', $time);
            if (!isset($tmp[$txt])) {
                $tmp[$txt] = 0;
            }
            foreach ($res as $v) {
                if ($v['user_register_time'] <= $time) {
                    $tmp[$txt]++;
                }
            }
        }
        $stat = [];
        foreach ($tmp as $k => $v) {
            $stat[] = ['date' => $k, 'qty' => $v];
        }
        return $stat;
    }

    /**
     * @return array
     * @throws DatabaseException
     */
    public function leagueGrow(): array
    {
        $res = DB::connect()
            ->table('league')
            ->field('id,apply_time')
            ->where(fn(Where $w) => $w->notEqualTo('status', LeagueStatus::DELETE))
            ->multi();
        $tmp = [];
        for ($i = 5; $i >= 0; $i--) {
            $d = date('Y-m-d H:i:s', strtotime(date('Y-m-01 23:59:59') . " -1day +1month -{$i}month"));
            $time = strtotime($d);
            $txt = date('Y年m月', $time);
            if (!isset($tmp[$txt])) {
                $tmp[$txt] = 0;
            }
            foreach ($res as $v) {
                if ($v['league_apply_time'] <= $time) {
                    $tmp[$txt]++;
                }
            }
        }
        $stat = [];
        foreach ($tmp as $k => $v) {
            $stat[] = ['date' => $k, 'qty' => $v];
        }
        return $stat;
    }


}