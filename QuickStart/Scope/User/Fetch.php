<?php

namespace Yonna\QuickStart\Scope\User;

use QuickStart\Mapping\User\UserStatus;
use Yonna\Database\DB;
use Yonna\Database\Driver\Pdo\Where;
use Yonna\QuickStart\Scope\AbstractScope;
use Yonna\Throwable\Exception\DatabaseException;

class Fetch extends AbstractScope
{

    /**
     * 获取列表
     * @return array
     * @throws DatabaseException
     */
    public function list(): array
    {
        $db = DB::connect()
            ->table('user')
            ->where(fn(Where $cond) => $cond->notEqualTo('uid', 1)->notEqualTo('status', UserStatus::DELETE));
        $whereSet = [
            'user' => [
                'equalTo' => [
                    'source',
                ],
                'in' => [
                    'uid',
                    'inviter_uid',
                    'status',
                ],
                'notIn' => [
                    'not_uid',
                    'not_status',
                ],
                'between' => [
                    'register_time',
                ],
            ],
        ];
        $db->complex($whereSet, $this->input());
        if ($this->input('order_by')) {
            $db->orderByStr($this->input('order_by'));
        } else {
            $db->orderBy('uid', 'desc', 'user');
        }
        if ($this->input('page')) {
            $result = $db->page($this->input('page_current'), $this->input('page_per'));
        } else {
            $result = $db->multi();
        }
        return $result ?: [];
    }

    /**
     * 获取详情
     * @return array
     * @throws DatabaseException
     */
    public function info(): array
    {
        if (!$this->input('uid')) {
            return [];
        }
        $result = DB::connect()->table('user')->field('uid,status,source,register_time')
            ->equalTo('uid', $this->input('uid'))
            ->one();
        return $result ?: [];
    }


}