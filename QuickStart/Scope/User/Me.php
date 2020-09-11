<?php

namespace Yonna\QuickStart\Scope\User;

use Yonna\Database\DB;
use Yonna\Database\Driver\Pdo\Where;
use Yonna\QuickStart\Helper\Password;
use Yonna\QuickStart\Scope\AbstractScope;
use Yonna\Throwable\Exception;

class Me extends AbstractScope
{

    const TABLE = 'user';

    /**
     * 获取当前登录用户详情
     * @return array
     * @throws Exception\ThrowException
     */
    public function one(): array
    {
        $result = ['user_id' => $this->request()->getLoggingId()];
        $meta = $this->scope(Meta::class, 'me');
        $result = array_merge($result, $meta);
        return $result;
    }

    /**
     * @return int
     * @throws Exception\DatabaseException
     * @throws Exception\ParamsException
     */
    public function update()
    {
        $pwd = $this->input('password');
        if ($pwd) {
            if (!Password::check($pwd)) {
                Exception::params(Password::getFalseMsg());
            }
            $pwd = Password::parse($pwd);
        }
        $data = [
            'password' => $pwd,
            'inviter_user_id' => $this->input('inviter_user_id'),
        ];
        if ($data) {
            return DB::connect()->table(self::TABLE)
                ->where(fn(Where $w) => $w->equalTo('id', $this->request()->getLoggingId()))
                ->update($data);
        }
        return true;
    }


}