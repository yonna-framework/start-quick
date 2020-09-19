<?php

namespace Yonna\QuickStart\Mapping\League;

use Yonna\Mapping\Mapping;

class LeagueTaskJoinerStatus extends Mapping
{

    const ABORT = -2;
    const GIVE_UP = -1;
    const DOING = 1;
    const COMPLETE = 2;

    public function __construct()
    {
        self::setLabel(self::ABORT, '中止');
        self::setLabel(self::GIVE_UP, '中途放弃');
        self::setLabel(self::DOING, '进行中');
        self::setLabel(self::COMPLETE, '完成');
    }

}