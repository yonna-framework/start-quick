<?php

namespace Yonna\QuickStart\Scope;

use Throwable;
use Yonna\QuickStart\Mapping\League\LeagueStatus;
use Yonna\QuickStart\Mapping\League\LeagueTaskStatus;
use Yonna\QuickStart\Prism\LeagueTaskAssignPrism;
use Yonna\QuickStart\Prism\LeagueTaskPrism;
use Yonna\Database\DB;
use Yonna\Database\Driver\Pdo\Where;
use Yonna\Throwable\Exception;
use Yonna\Validator\ArrayValidator;

/**
 * Class LeagueTaskAssign
 * @package Yonna\QuickStart\Scope
 */
class LeagueTaskAssign extends AbstractScope
{

    const TABLE = 'league_task_assign';

    /**
     * @return int
     * @throws Throwable
     */
    public function insert()
    {
        ArrayValidator::required($this->input(), ['task_id', 'league_id'], function ($error) {
            Exception::throw($error);
        });
        $prism = new LeagueTaskAssignPrism($this->request());
        $add = [
            'task_id' => $prism->getTaskId(),
            'league_id' => $prism->getLeagueId(),
            'assign_time' => time(),
        ];
        return DB::connect()->table(self::TABLE)->insert($add);
    }

    /**
     * @return int
     * @throws Exception\DatabaseException
     */
    public function delete()
    {
        ArrayValidator::required($this->input(), ['task_id', 'league_id'], function ($error) {
            Exception::throw($error);
        });
        return DB::connect()->table(self::TABLE)
            ->where(fn(Where $w) => $w
                ->equalTo('task_id', $this->input('task_id'))
                ->equalTo('league_id', $this->input('league_id'))
            )
            ->delete();
    }

}