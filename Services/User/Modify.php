<?php

namespace Yonna\QuickStart\Services\User;

use App\Helper\LoginName;
use App\Scope\AbstractScope;
use Throwable;
use Yonna\Database\DB;
use Yonna\Throwable\Exception;
use Yonna\Validator\ArrayValidator;

class Modify extends abstractScope
{

    /**
     * 修改登录名
     * @return bool
     * @throws Exception\DatabaseException
     * @throws Exception\ParamsException
     */
    public function info(): bool
    {
        ArrayValidator::required($this->input(), ['uid', 'login_name'], function ($error) {
            Exception::params($error);
        });
        if (!LoginName::check($this->input('login_name'))) {
            Exception::params(LoginName::getFalseMsg());
        }
        $used = DB::connect()->table('user')->equalTo('login_name', $this->input('login_name'))->one();
        if ($used) {
            if ($used['user_uid'] == $this->input('uid')) {
                Exception::params('You can\'t use the same name.');
            } else {
                Exception::params('Name has been used');
            }
        }
        try {
            DB::connect()->table('user')->equalTo('uid', $this->input('uid'))->update([
                'login_name' => $this->input('login_name')
            ]);
        } catch (Throwable $e) {
            Exception::database($e);
        }
        return true;
    }

    /**
     * 修改密码
     * @return bool
     * @throws Exception\DatabaseException
     * @throws Exception\ParamsException
     */
    public function changePassword(): bool
    {
        if (!$this->input('uid')) {
            Exception::params('not uid');
        }
        if (!$this->input('login_password')) {
            Exception::params('not login_password');
        }
        if (!LoginName::check($this->input('login_name'))) {
            Exception::params(LoginName::getFalseMsg());
        }
        $used = DB::connect()->table('user')->equalTo('login_name', $this->input('login_name'))->one();
        if ($used) {
            if ($used['user_uid'] == $this->input('uid')) {
                Exception::params('You can\'t use the same name.');
            } else {
                Exception::params('Name has been used');
            }
        }
        try {
            DB::connect()->table('user')->equalTo('uid', $this->input('uid'))->update([
                'login_name' => $this->input('login_name')
            ]);
        } catch (Throwable $e) {
            Exception::database($e);
        }
        return true;
    }


}