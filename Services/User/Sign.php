<?php

namespace Yonna\QuickStart\Services\User;

use App\Helper\Password;
use App\Mapping\Common\IsSure;
use App\Mapping\User\AccountType;
use App\Mapping\User\Status;
use App\Scope\AbstractScope;
use Throwable;
use Yonna\Database\DB;
use Yonna\Services\Log\Log;
use Yonna\Throwable\Exception;

/**
 * Class Sign
 * @package App\Log\User
 */
class Sign extends abstractScope
{

    const ONLINE_KEEP_TIME = 86400;
    const ONLINE_REDIS_KEY = 'user:online:';

    /**
     * 登录记录
     * @param $userInfo
     * @return bool
     * @throws Exception\DatabaseException
     * @throws Exception\ParamsException
     */
    private function loginRecord($userInfo)
    {
        if (!$userInfo['user_uid']) {
            Exception::params('登录记录参数不全');
        }
        // 写日志
        $input = $this->input();
        $input['password'] = '*';
        $log = [
            'uid' => $userInfo['user_uid'],
            'ip' => $this->request()->getIp(),
            'client_id' => $this->request()->getClientId(),
            'input' => $input,
        ];
        Log::db()->info($log, 'login');
        // 设定uid为登录状态
        $onlineKey = self::ONLINE_REDIS_KEY . $log['uid'];
        if (DB::redis()->get($onlineKey) === 'online') {
            DB::redis()->expire($onlineKey, self::ONLINE_KEEP_TIME);
        } else {
            DB::redis()->set($onlineKey, 'online', self::ONLINE_KEEP_TIME);
        }
        return true;
    }

    /**
     * @return bool
     * @throws null
     */
    public function isLogin()
    {
        $onlineKey = self::ONLINE_REDIS_KEY . $this->input('auth_uid');
        $res = DB::redis()->get($onlineKey) ?? null;
        return $res === 'online';
    }

    /**
     * logout
     * @return mixed
     * @throws Throwable
     */
    public function out()
    {
        $onlineKey = self::ONLINE_REDIS_KEY . $this->input('auth_uid');
        DB::redis()->delete($onlineKey);
        return true;
    }

    /**
     * @return mixed
     * @throws Throwable
     */
    public function in()
    {
        $account = $this->input('account');
        $password = $this->input('password');
        if (!$account) {
            Exception::params("error account");
        }
        if (!$password) {
            Exception::params("error password");
        }
        // 看看账号是否存在
        $accounts = DB::connect()
            ->table('user_account')
            ->field('uid')
            ->in('type', [AccountType::NAME, AccountType::PHONE, AccountType::EMAIL,])
            ->equalTo('string', $account)
            ->equalTo('allow_login', IsSure::yes)
            ->one();
        if (empty($accounts['user_account_uid'])) {
            Exception::params("Account does not exist");
        }
        $uid = $accounts['user_account_uid'];
        $userInfo = DB::connect()
            ->table('user')
            ->field('uid,status,password')
            ->equalTo('uid', $uid)
            ->one();
        // 检查账号状态
        switch ($userInfo['user_status']) {
            case Status::FREEZE:
                Exception::throw('Account has been frozen');
                break;
            case Status::UNVERIFY:
                Exception::throw('Account has not been approved, please wait for approval');
                break;
            case Status::UNPASS:
                Exception::throw('Account audit failed');
                break;
            default:
                break;
        }
        // 检查许可
        $userLicense = DB::connect()
            ->table('user_license')
            ->field('license_id')
            ->equalTo('uid', $uid)
            ->lessThanOrEqualTo('start_time', date('Y-m-d H:i:s', time()))
            ->greaterThanOrEqualTo('end_time', date('Y-m-d H:i:s', time()))
            ->multi();
        if (!$userLicense) {
            Exception::throw("Account not any licensed");
        }
        $userLicenseIds = array_column($userLicense, 'user_license_license_id');
        if (in_array(1, $userLicenseIds)) {
            //super account
        } else {
            Exception::throw("Account not licensed");
        }
        // 检查密码
        if (empty($userInfo['user_password']) || $userInfo['user_password'] !== Password::parse($password)) {
            Exception::throw('Wrong password');
        }
        // 记录登录信息
        try {
            $this->loginRecord($userInfo);
        } catch (Throwable $e) {
            Exception::origin($e);
        }
        return [
            'user_uid' => $uid,
            'user_account' => $account,
            'user_status' => $userInfo['user_status'],
        ];
    }

}