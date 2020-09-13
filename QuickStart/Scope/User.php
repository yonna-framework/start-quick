<?php

namespace Yonna\QuickStart\Scope;

use Yonna\QuickStart\Mapping\User\UserStatus;
use Yonna\Database\DB;
use Yonna\Database\Driver\Pdo\Where;
use Yonna\QuickStart\Prism\UserPrism;
use Yonna\Throwable\Exception;

class User extends AbstractScope
{

    const TABLE = 'user';

    /**
     * 获取详情
     * @return array
     * @throws Exception\DatabaseException
     */
    public function one(): array
    {
        if (!$this->input('id')) {
            return [];
        }
        $result = DB::connect()->table('user')->field('id,status,register_datetime')
            ->where(fn(Where $w) => $w->equalTo('id', $this->input('id')))
            ->one();
        return $result ?: [];
    }

    /**
     * 获取列表
     * @return array
     * @throws Exception\DatabaseException
     */
    public function multi(): array
    {
        $prism = new UserPrism($this->request());
        $db = DB::connect()
            ->table('user')
            ->where(function (Where $w) use ($prism) {
                $w->notEqualTo('status', UserStatus::DELETE);
                $prism->getId() && $w->equalTo('id', $prism->getId());
                $prism->getInviterUserId() && $w->equalTo('inviter_user_id', $prism->getInviterUserId());
                $prism->getStatus() && $w->equalTo('status', $prism->getStatus());
                $prism->getRegisterTime() && $w->between('register_time', $prism->getRegisterTime());
            });
        if ($this->input('order_by')) {
            $db->orderByStr($this->input('order_by'));
        } else {
            $db->orderBy('id', 'desc', 'user');
        }
        $result = $db->multi();
        return $result ?: [];
    }

}