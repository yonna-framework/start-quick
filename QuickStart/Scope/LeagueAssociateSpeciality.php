<?php

namespace Yonna\QuickStart\Scope;

use Yonna\QuickStart\Prism\LeagueAssociateDataPrism;
use Yonna\Database\DB;
use Yonna\Database\Driver\Pdo\Where;
use Yonna\Throwable\Exception;
use Yonna\Validator\ArrayValidator;

/**
 * Class League
 * @package Yonna\QuickStart\Scope
 */
class LeagueAssociateSpeciality extends AbstractScope
{

    const TABLE = 'league_associate_speciality';

    /**
     * @return mixed
     * @throws Exception\DatabaseException
     */
    public function multi(): array
    {
        $prism = new LeagueAssociateDataPrism($this->request());
        return DB::connect()->table(self::TABLE)
            ->where(function (Where $w) use ($prism) {
                $prism->getLeagueId() && $w->equalTo('league_id', $prism->getLeagueId());
            })
            ->multi();
    }

    /**
     * @return bool
     * @throws \Throwable
     */
    public function cover()
    {
        ArrayValidator::required($this->input(), ['league_id'], function ($error) {
            Exception::throw($error);
        });
        $league_id = $this->input('league_id');
        $data = $this->input('data');
        $add = [];
        if ($data) {
            foreach ($data as $d) {
                $add[] = [
                    'league_id' => $league_id,
                    'data_id' => $d,
                ];
            }
        }
        DB::transTrace(function () use ($league_id, $add) {
            DB::connect()->table(self::TABLE)->where(fn(Where $w) => $w->equalTo('league_id', $league_id))->delete();
            if ($add) {
                DB::connect()->table(self::TABLE)->insertAll($add);
            }
        });
        return true;
    }

}