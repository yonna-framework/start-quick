<?php

namespace Yonna\QuickStart\Scope;

use Throwable;
use Yonna\QuickStart\Mapping\Common\Boolean;
use Yonna\QuickStart\Mapping\League\LeagueIsAdmin;
use Yonna\QuickStart\Mapping\League\LeagueStatus;
use Yonna\QuickStart\Prism\LeagueMemberPrism;
use Yonna\Database\DB;
use Yonna\Database\Driver\Pdo\Where;
use Yonna\Throwable\Exception;
use Yonna\Validator\ArrayValidator;

/**
 * Class LeagueMember
 * @package Yonna\QuickStart\Scope
 */
class LeagueMember extends AbstractScope
{

    const TABLE = 'league_member';

    /**
     * @return mixed
     * @throws Exception\DatabaseException
     */
    public function multi(): array
    {
        $prism = new LeagueMemberPrism($this->request());
        return DB::connect()->table(self::TABLE)
            ->where(function (Where $w) use ($prism) {
                $prism->getLeagueId() && $w->equalTo('league_id', $prism->getLeagueId());
                $prism->getUserId() && $w->equalTo('user_id', $prism->getUserId());
                $prism->getStatus() && $w->equalTo('status', $prism->getStatus());
            })
            ->orderBy('league_id', 'desc')
            ->multi();
    }

    /**
     * @return mixed
     * @throws Exception\DatabaseException
     */
    public function page(): array
    {
        $prism = new LeagueMemberPrism($this->request());
        return DB::connect()->table(self::TABLE)
            ->where(function (Where $w) use ($prism) {
                $prism->getLeagueId() && $w->equalTo('league_id', $prism->getLeagueId());
                $prism->getUserId() && $w->equalTo('user_id', $prism->getUserId());
                $prism->getStatus() && $w->equalTo('status', $prism->getStatus());
            })
            ->orderBy('league_id', 'desc')
            ->page($prism->getCurrent(), $prism->getPer());
    }

    /**
     * @return bool|mixed|null
     * @throws Exception\DatabaseException
     * @throws Throwable
     */
    public function insert()
    {
        ArrayValidator::required($this->input(), ['league_id', 'user_id'], function ($error) {
            Exception::throw($error);
        });
        $prism = new LeagueMemberPrism($this->request());
        $one = DB::connect()->table(self::TABLE)
            ->where(fn(Where $w) => $w->equalTo('league_id', $prism->getLeagueId())->equalTo('user_id', $prism->getUserId()))
            ->one();
        if ($one) {
            return true;
        }
        $add = [
            'league_id' => $prism->getLeagueId(),
            'user_id' => $prism->getUserId(),
            'is_admin' => $prism->getIsAdmin() ?? LeagueIsAdmin::NO,
            'status' => $prism->getStatus() ?? LeagueStatus::PENDING,
            'apply_reason' => $prism->getApplyReason() ?? '',
            'apply_time' => time(),
            'rejection_time' => 0,
            'pass_time' => 0,
            'delete_time' => 0,
        ];
        return DB::connect()->table(self::TABLE)->insert($add);
    }

    /**
     * @return int
     * @throws Exception\DatabaseException
     */
    public function update()
    {
        ArrayValidator::required($this->input(), ['league_id', 'user_id'], function ($error) {
            Exception::throw($error);
        });
        $prism = new LeagueMemberPrism($this->request());
        $one = DB::connect()->table(self::TABLE)
            ->where(fn(Where $w) => $w->equalTo('league_id', $prism->getLeagueId())->equalTo('user_id', $prism->getUserId()))
            ->one();
        if (!$one) {
            return 0;
        }
        $data = [
            'is_admin' => $prism->getIsAdmin(),
            'status' => $prism->getStatus(),
        ];
        switch ($prism->getStatus()) {
            case LeagueStatus::REJECTION:
                $data['rejection_time'] = time();
                $data['rejection_reason'] = $prism->getReason();
                break;
            case LeagueStatus::APPROVED:
                $data['pass_time'] = time();
                $data['pass_reason'] = $prism->getReason();
                break;
            case LeagueStatus::DELETE:
                $data['delete_time'] = time();
                $data['delete_reason'] = $prism->getReason();
                break;
        }
        return DB::connect()->table(self::TABLE)
            ->where(fn(Where $w) => $w
                ->equalTo('league_id', $prism->getLeagueId())
                ->equalTo('user_id', $prism->getUserId())
            )
            ->update($data);
    }

    /**
     * @return int
     * @throws Exception\DatabaseException
     */
    public function status()
    {
        ArrayValidator::required($this->input(), ['league_id', 'user_id'], function ($error) {
            Exception::throw($error);
        });
        $status = $this->input('status');
        $reason = $this->input('reason');
        $data = ['status' => $status];
        switch ($status) {
            case LeagueStatus::REJECTION:
                $data['rejection_time'] = time();
                $data['rejection_reason'] = $reason;
                break;
            case LeagueStatus::APPROVED:
                $data['pass_time'] = time();
                $data['pass_reason'] = $reason;
                break;
            case LeagueStatus::DELETE:
                $data['delete_time'] = time();
                $data['delete_reason'] = $reason;
                break;
        }
        return DB::connect()->table(self::TABLE)
            ->where(fn(Where $w) => $w
                ->equalTo('league_id', $this->input('league_id'))
                ->equalTo('user_id', $this->input('user_id'))
            )
            ->update($data);
    }

    /**
     * @return false|int
     * @throws Exception\DatabaseException
     */
    public function delete()
    {
        ArrayValidator::required($this->input(), ['league_id', 'user_id'], function ($error) {
            Exception::throw($error);
        });
        return DB::connect()->table(self::TABLE)
            ->where(fn(Where $w) => $w
                ->equalTo('league_id', $prism->getLeagueId())
                ->equalTo('user_id', $prism->getUserId())
            )
            ->delete();
    }

}