<?php

namespace Yonna\QuickStart\Scope\Project;

use Yonna\QuickStart\Mapping\User\UserStatus;
use Yonna\QuickStart\Scope\AbstractScope;
use Yonna\QuickStart\Scope\User\Stat as UserStat;
use Yonna\Throwable\Exception;

class Index extends AbstractScope
{


    /**
     * @return array
     * @throws Exception\ThrowException
     */
    public function stat(): array
    {
        $stat = [
            'user' => [],
        ];
        foreach (UserStatus::toKv('label') as $k => $v) {
            $stat['user'][$k] = [
                'qty' => 0,
                'label' => $v,
            ];
        }
        $userCount = $this->scope(UserStat::class, 'count');
        foreach ($userCount as $u) {
            $stat['user'][$u['user_status']]['qty'] = $u['qty'];
        }
        return $stat;
    }


}