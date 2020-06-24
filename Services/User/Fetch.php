<?php

namespace Yonna\QuickStart\Services\User;

use App\Mapping\Common\IsSure;
use App\Scope\AbstractScope;
use Yonna\Database\DB;
use Yonna\Throwable\Exception\DatabaseException;

class Fetch extends abstractScope
{

    /**
     * 获取列表
     * @return array
     * @throws DatabaseException
     */
    public function list(): array
    {
        $db = DB::connect()->table('user');
        $db->notEqualTo('uid', 1)->notEqualTo('status', IsSure::no);
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