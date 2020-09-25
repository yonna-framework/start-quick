<?php

namespace Yonna\QuickStart\Scope;

use Yonna\Database\DB;
use Yonna\Foundation\Str;
use Yonna\QuickStart\Helper\Assets;
use Yonna\QuickStart\Mapping\User\AccountType;
use Yonna\Throwable\Exception;
use Yonna\Validator\ArrayValidator;

/**
 * Class UserWechat
 * @package Yonna\QuickStart\Scope
 */
class UserWechat extends AbstractScope
{

    /**
     * @return mixed
     * @throws Exception\ThrowException
     */
    public function bind()
    {
        ArrayValidator::required($this->input(), ['phone'], function ($error) {
            Exception::throw($error);
        });
        $openid = $this->request()->getLoggingId();
        if (!is_string($openid)) {
            Exception::throw('openid error');
        }
        $checkOpenId = $this->scope(UserAccount::class, 'one', ['string' => $openid]);
        if ($checkOpenId) {
            Exception::throw('openid already bind');
        }
        $sdk = $this->scope(SdkWxmpUser::class, 'one', ['openid' => $openid]);
        if (!$sdk) {
            Exception::throw('wechat user is not authenticated');
        }
        $phone = $this->input('phone');
        $metas = $this->input('metas');
        // 合并一下从微信获得的数据，如果用户有自定义则跳过
        if (empty($metas['sex']) && !empty($sdk['sdk_wxmp_user_sex'])) {
            $metas['sex'] = (int)$sdk['sdk_wxmp_user_sex'];
        }
        if (empty($metas['name']) && !empty($sdk['sdk_wxmp_user_nickname'])) {
            $metas['name'] = $sdk['sdk_wxmp_user_nickname'];
        }
        if (empty($metas['avatar']) && !empty($sdk['sdk_wxmp_user_headimgurl'])) {
            $src = Assets::getUrlSource($sdk['sdk_wxmp_user_headimgurl']);
            if ($src) {
                $res = (new Xoss($this->request()))->saveFile($src);
                if ($res) {
                    $metas['avatar'] = [$res['xoss_key']];
                }
            }
        }
        DB::transTrace(function () use ($phone, $openid, $metas) {
            $find = $this->scope(UserAccount::class, 'one', ['string' => $phone]);
            $user_id = $find['user_account_user_id'] ?? null;
            // 有数据则绑定原有账号，没数据则新建账号
            if ($user_id) {
                // 记录openid
                $this->scope(UserAccount::class, 'insert', [
                    'user_id' => $user_id,
                    'string' => $openid,
                    'type' => AccountType::WX_OPEN_ID,
                ]);
                // 更新数据
                $data = [
                    'id' => $user_id,
                    'metas' => $metas,
                ];
                $this->scope(User::class, 'update', $data);
            } else {
                $data = [
                    'password' => Str::randomLetter(10),
                    'accounts' => [$phone => AccountType::PHONE],
                    'metas' => $metas,
                ];
                $this->scope(User::class, 'insert', $data);
            }
            Exception::throw('666');
        });
        return true;
    }


}