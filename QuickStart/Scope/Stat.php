<?php

namespace Yonna\QuickStart\Scope;

use Yonna\Database\DB;
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


}