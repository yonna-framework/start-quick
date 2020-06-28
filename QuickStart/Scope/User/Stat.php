<?php

namespace Yonna\QuickStart\Scope\User;

use Yonna\Database\DB;
use Yonna\QuickStart\Scope\AbstractScope;
use Yonna\Throwable\Exception\DatabaseException;

class Stat extends AbstractScope
{


    /**
     * @return array
     * @throws DatabaseException
     */
    public function count(): array
    {
        $res = DB::connect()
            ->table('user')
            ->field('count(`id`) as qty,status')
            ->groupBy('status')
            ->multi();
        return $res;
    }


}