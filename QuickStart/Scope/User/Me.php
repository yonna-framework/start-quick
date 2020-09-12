<?php

namespace Yonna\QuickStart\Scope\User;

use Yonna\Database\DB;
use Yonna\Database\Driver\Pdo\Where;
use Yonna\QuickStart\Helper\Password;
use Yonna\QuickStart\Mapping\Common\Boolean;
use Yonna\QuickStart\Mapping\User\MetaValueFormat;
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
        $values = $this->scope(Meta::class, 'multi', ['user_id' => $this->request()->getLoggingId()]);
        $category = $this->scope(MetaCategory::class, 'multi', ['status' => Boolean::true]);
        $info = [
            'user_id' => $this->request()->getLoggingId(),
        ];
        foreach ($category as $c) {
            $k = $c['user_meta_category_key'];
            $v = $values[$k] ?? $c['user_meta_category_value_default'];
            switch ($c['user_meta_category_value_format']) {
                case MetaValueFormat::INTEGER:
                    $v = $v ? (int)$v : 0;
                    break;
                case MetaValueFormat::FLOAT1:
                    $v = $v ? round($v, 1) : 0.0;
                    break;
                case MetaValueFormat::FLOAT2:
                    $v = $v ? round($v, 2) : 0.00;
                    break;
                case MetaValueFormat::FLOAT3:
                    $v = $v ? round($v, 3) : 0.000;
                    break;
                case MetaValueFormat::DATE:
                    if (is_numeric($v)) {
                        $v = date('Y-m-d', $v);
                    } else {
                        $v = $v ? $v : '1970-01-01';
                    }
                    break;
                case MetaValueFormat::TIME:
                    if (is_numeric($v)) {
                        $v = date('H:i:s', $v);
                    } else {
                        $v = $v ? $v : '00:00:00';
                    }
                    break;
                case MetaValueFormat::DATETIME:
                    if (is_numeric($v)) {
                        $v = date('Y-m-d H:i:s', $v);
                    } else {
                        $v = $v ? $v : '1970-01-01 00:00:00';
                    }
                    break;
                case MetaValueFormat::STRING:
                default:
                    $v = $v ? (string)$v : '';
                    break;
            }
            $info['user_meta_' . $k] = $v;
        }
        return $info;
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