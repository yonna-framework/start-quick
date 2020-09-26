<?php

namespace Yonna\QuickStart\Mapping\League;

use Yonna\Mapping\Mapping;

class LeagueMemberPermission extends Mapping
{

    const MEMBER = 1;
    const MANAGER = 5;
    const MASTER = 10;

    public function __construct()
    {
        self::setLabel(self::MEMBER, '成员');
        self::setLabel(self::MANAGER, '管理员');
        self::setLabel(self::MASTER, '拥有者');
    }

}