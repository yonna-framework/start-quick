<?php

namespace Yonna\QuickStart\Scope\User;

use Yonna\QuickStart\Mapping\User\UserStatus;
use Yonna\Database\DB;
use Yonna\Database\Driver\Pdo\Where;
use Yonna\QuickStart\Scope\AbstractScope;
use Yonna\Throwable\Exception;

class AdminFetch extends AbstractScope
{

    /**
     * 获取列表
     * @return array
     * @throws Exception\DatabaseException
     */
    public function list(): array
    {
        $db = DB::connect()
            ->table('user')
            ->where(function (Where $cond) {
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
                $cond
                    ->notEqualTo('uid', 1)->notEqualTo('status', UserStatus::DELETE)
                    ->complex($whereSet, $this->input());
            });
        if ($this->input('order_by')) {
            $db->orderByStr($this->input('order_by'));
        } else {
            $db->orderBy('uid', 'desc', 'user');
        }
        if ($this->input('page')) {
            $result = $db->page($this->input('current'), $this->input('per'));
        } else {
            $result = $db->multi();
        }
        return $result ?: [];
    }

    /**
     * 获取当前登陆用户详情
     * @return array
     * @throws Exception\ThrowException|Exception\DatabaseException
     */
    public function me(): array
    {
        $result = ['user_id' => $this->request()->getLoggingId()];
        $meta = $this->scope(Meta::class, 'me');
        $result = array_merge($result, $meta);
        return $result;
    }

    /**
     * 获取详情
     * @return array
     * @throws Exception\DatabaseException
     */
    public function info(): array
    {
        if (!$this->input('id')) {
            return [];
        }
        $result = DB::connect()->table('user')->field('id,status,register_datetime')
            ->where(fn(Where $w) => $w->equalTo('id', $this->input('id')))
            ->one();
        return $result ?: [];
    }


}