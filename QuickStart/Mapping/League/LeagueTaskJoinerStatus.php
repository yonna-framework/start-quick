<?php

namespace Yonna\QuickStart\Mapping\League;

use Yonna\Mapping\Mapping;

class LeagueTaskJoinerStatus extends Mapping
{

    const ABORT = -2;
    const GIVE_UP = -1;
    const PENDING = 1;
    const APPROVED = 5;
    const COMPLETE = 10;

    public function __construct()
    {
        self::setLabel(self::ABORT, '中止');
        self::setLabel(self::GIVE_UP, '中途放弃');
        self::setLabel(self::PENDING, '待审核');
        self::setLabel(self::APPROVED, '进行中');
        self::setLabel(self::COMPLETE, '完成');
    }

}