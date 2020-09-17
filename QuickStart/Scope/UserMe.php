<?php

namespace Yonna\QuickStart\Scope;

use Yonna\Database\DB;
use Yonna\Database\Driver\Pdo\Where;
use Yonna\QuickStart\Helper\Password;
use Yonna\QuickStart\Mapping\Common\Boolean;
use Yonna\QuickStart\Mapping\User\MetaValueFormat;
use Yonna\Throwable\Exception;

class UserMe extends AbstractScope
{

    const TABLE = 'user';

    /**
     * 获取当前登录用户详情
     * @return array
     * @throws Exception\ThrowException
     */
    public function one(): array
    {
        $values = $this->scope(UserMeta::class, 'multi', ['user_id' => $this->request()->getLoggingId()]);
        $category = $this->scope(UserMetaCategory::class, 'multi', ['status' => Boolean::true]);
        $info = [
            'user_id' => $this->request()->getLoggingId(),
        ];
        foreach ($category as $c) {
            $k = $c['user_meta_category_key'];
            $v = $values[$k] ?? $c['user_meta_category_value_default'];
            $info['user_meta_' . $k] = UserMetaCategory::valueFormat($v, $c['user_meta_category_value_format']);
        }
        return $info;
    }

    /**
     * @return int
     * @throws Exception\DatabaseException
     * @throws Exception\ParamsException
     */
    public function password()
    {
        $pwd = $this->input('password');
        if ($pwd) {
            if (!Password::check($pwd)) {
                Exception::params(Password::getFalseMsg());
            }
            $pwd = Password::parse($pwd);
        }
        $data = ['password' => $pwd];
        if ($data) {
            return DB::connect()->table(self::TABLE)
                ->where(fn(Where $w) => $w->equalTo('id', $this->request()->getLoggingId()))
                ->update($data);
        }
        return true;
    }


}