<?php

namespace Yonna\QuickStart\Scope;

use Yonna\Throwable\Exception;

/**
 * Class Wxmp
 * @package Yonna\QuickStart\Scope
 */
class SdkWxmp extends AbstractScope
{

    /**
     * 获取 AccessToken
     * @param $config
     * @param null $wxCode
     * @return array|bool|null
     * @throws Exception\ParamsException
     */
    private function getTokenAuthUserInfo($config, $wxCode = null)
    {
        if (!$config) {
            Exception::params('config error');
        }
        $this->lastConfig = $config;
        $externalConfigKV = $this->getExternalKV($config, true);
        if (empty($externalConfigKV)) {
            return $this->false('错误配置');
        }
        $thisConfig = $externalConfigKV['wxmp'];
        if (!$thisConfig['appid']) return $this->false('无效的' . $thisConfig['appid_label']);
        if (!$thisConfig['secret']) return $this->false('无效的' . $thisConfig['secret_label']);
        $accessToken = null;
        try {
            $accessToken = $this->redis()->get("WxmpModel#AccessToken#User{$this->getClientID()}{$config}");
            if (!$accessToken) {
                if (!$wxCode) {
                    return null;
                }
                $url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid={$thisConfig['appid']}";
                $url .= "&secret={$thisConfig['secret']}";
                $url .= "&code={$wxCode}";
                $url .= "&grant_type=authorization_code";
                $accessToken = curlGet($url, 5.00);
                $accessToken = json_decode($accessToken, true);
                if (!empty($accessToken['errcode'])) {
                    return $this->false($accessToken['errcode']);
                }
                $this->redis()->set("WxmpModel#AccessToken#User{$this->getClientID()}{$config}", $accessToken, $accessToken['expires_in'] - 100);
            }
        } catch (\Exception $e) {
            return $this->false($e->getMessage());
        }
        return $accessToken;
    }

    public function login()
    {
        $bean = $this->getBean();
        if (!$bean->getExternalConfig()) return $this->error('not config');
        $extra = $bean->getExtra() ?: array();
        $behaviour = $bean->getBehaviour();
        $action = null;
        switch ($behaviour) {
            case 'login.step1':
                if (!$bean->getReturnUrl()) return $this->error('not return url');
                $tokenAuth = $this->getTokenAuthUserInfo($bean->getExternalConfig());
                if ($tokenAuth === false) {
                    return $this->error($this->getFalseMsg());
                }
                if (!$tokenAuth) {
                    $action = 'login.step2';
                } else {
                    $action = 'login.step3';
                }
                break;
            case 'login.step2':
                if (!$bean->getCode()) return $this->error('not code');
                $externalConfigKV = $this->getExternalKV($bean->getExternalConfig(), true);
                if (empty($externalConfigKV)) {
                    return $this->error('配置错误');
                }
                $tokenAuth = $this->getTokenAuthUserInfo($bean->getExternalConfig(), $bean->getCode());
                $action = 'login.step3';
                break;
            default:
                return $this->error('not allow');
                break;
        }
        switch ($action) {
            case 'login.step2': // 跳到步骤2
                $externalConfigKV = $this->getExternalKV($bean->getExternalConfig(), true);
                if (empty($externalConfigKV)) {
                    return $this->error('配置错误');
                }
                $thisConfig = $externalConfigKV['wxmp'];
                if (!$thisConfig['appid']) return $this->error('无效的' . $thisConfig['appid_label']);
                $url = $this->getHost() . '/external/wxmpLoginStep2';
                $url .= '?return_url=' . urlencode($bean->getReturnUrl());
                $url .= '&platform=' . $this->getPlatform();
                $url .= '&client_id=' . $this->getClientID();
                $url .= '&external_config=' . $bean->getExternalConfig();
                $url .= '&extra=' . urlencode(json_encode($bean->getExtra()));
                //todo 微信配置
                $wxConf = array(
                    'appid' => $thisConfig['appid'],
                    'redirect_uri' => urlencode($url),
                    'response_type' => 'code',
                    'scope' => 'snsapi_userinfo',
                    'state' => $bean->getState() ?: randChar(10),
                );
                $url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid={$wxConf['appid']}";
                $url .= "&redirect_uri={$wxConf['redirect_uri']}";
                $url .= "&response_type={$wxConf['response_type']}";
                $url .= "&scope={$wxConf['scope']}";
                $url .= "&state={$wxConf['state']}";
                $url .= "#wechat_redirect";
                return $this->success($url);
                break;
            case 'login.step3': // 获取微信数据，并自动注册登录
                // TODO 获取snsapi_userinfo
                if (!$tokenAuth) {
                    return $this->error('not tokenAuth');
                }
                $access_token = $tokenAuth['access_token'];
                //$expires_in = $tokenAuth['expires_in'];
                //$refresh_token = $tokenAuth['refresh_token'];
                $openid = $tokenAuth['openid'];
                //$scope = $tokenAuth['scope'];
                try {
                    $wxUserInfo = $this->redis()->get("WxmpModel#LoginStep3#{$openid}");
                    if (!$wxUserInfo) {
                        $url = "https://api.weixin.qq.com/sns/userinfo?access_token={$access_token}&openid={$openid}&lang=zh_CN";
                        $wxUserInfo = curlGet($url, 5.00);
                        $wxUserInfo = json_decode($wxUserInfo, true);
                        if (isset($wxUserInfo['errcode'])) {
                            if (strpos($wxUserInfo['errmsg'], 'access_token is invalid or not latest') !== false) {
                                $this->clearToken(self::TOKEN_USER);
                                return $this->getUserInfo(); // retry
                            } else {
                                throw new \Exception($wxUserInfo['errcode']);
                            }
                        }
                        $this->redis()->set("WxmpModel#LoginStep3#{$openid}", $wxUserInfo, 300);
                    }
                } catch (\Exception $e) {
                    return $this->error($e->getMessage());
                }
                // todo 记录微信信息
                $actualConfig = $this->getActualConfig($bean->getExternalConfig());
                $wxData = array(
                    'unionid' => $wxUserInfo['unionid'] ?? '',
                    'sex' => $wxUserInfo['sex'],
                    'nickname' => $wxUserInfo['nickname'],
                    'avatar' => $wxUserInfo['headimgurl'],
                    'language' => $wxUserInfo['language'],
                    'city' => $wxUserInfo['city'],
                    'province' => $wxUserInfo['province'],
                    'country' => $wxUserInfo['country']
                );
                $one = $this->db()->table('external_wx_user_info')
                    ->field('open_id')
                    ->equalTo('config', $actualConfig)
                    ->equalTo('open_id', $wxUserInfo['openid'])
                    ->one();
                try {
                    if (!empty($one['external_wx_user_info_open_id']) && $one['external_wx_user_info_open_id'] == $wxUserInfo['openid']) {
                        $this->db()->table('external_wx_user_info')
                            ->equalTo('config', $actualConfig)
                            ->equalTo('open_id', $wxUserInfo['openid'])
                            ->update($wxData);
                    } else {
                        $wxData['config'] = $actualConfig;
                        $wxData['open_id'] = $wxUserInfo['openid'];
                        $this->db()->table('external_wx_user_info')->insert($wxData);
                    }
                } catch (\Exception $e) {
                    return $this->error($e->getMessage());
                }
                // 微信登录
                $wechatBean = new WechatBean();
                $wechatBean->setLoginName($extra['login_name'] ?? null);
                $wechatBean->setIdentityName($extra['identity_name'] ?? null);
                $wechatBean->setOpenId($wxUserInfo['openid']);
                $wechatBean->setUnionid($wxUserInfo['unionid'] ?? '');
                $wechatBean->setSex($wxUserInfo['sex']);
                $wechatBean->setLanguage($wxUserInfo['language']);
                $wechatBean->setNickname($wxUserInfo['nickname']);
                $wechatBean->setCity($wxUserInfo['city']);
                $wechatBean->setProvince($wxUserInfo['province']);
                $wechatBean->setCountry($wxUserInfo['country']);
                $wechatBean->setAvatar($wxUserInfo['headimgurl']);
                $wechatModel = new WechatModel($this->getIO());
                if (!$account = $wechatModel->wxLogin__($wechatBean)) {
                    return $this->error($wechatModel->getFalseMsg());
                }
                return $this->success($account);
                break;
            default:
                return $this->notFount('action not found');
                break;
        }
    }

}