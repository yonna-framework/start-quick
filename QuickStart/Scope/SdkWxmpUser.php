<?php

namespace Yonna\QuickStart\Scope;

use Yonna\Database\DB;
use Yonna\Database\Driver\Pdo\Where;
use Yonna\QuickStart\Prism\SdkWxmpUserPrism;
use Yonna\Throwable\Exception;
use Yonna\Validator\ArrayValidator;

/**
 * Class SdkWxmpUser
 * @package Yonna\QuickStart\Scope
 */
class SdkWxmpUser extends AbstractScope
{

    const TABLE = 'sdk_wxmp_user';

    /**
     * @return false|int
     * @throws Exception\DatabaseException
     */
    public function save()
    {
        ArrayValidator::required($this->input(), ['openid'], function ($error) {
            Exception::throw($error);
        });
        $prism = new SdkWxmpUserPrism($this->request());
        $one = DB::connect()->table(self::TABLE)
            ->where(fn(Where $w) => $w->equalTo('openid', $prism->getOpenid()))
            ->one();
        $data = [
            'unionid' => $prism->getUnionid(),
            'sex' => $prism->getSex(),
            'nickname' => $prism->getNickname(),
            'headimgurl' => $prism->getHeadimgurl(),
            'language' => $prism->getLanguage(),
            'city' => $prism->getCity(),
            'province' => $prism->getProvince(),
            'country' => $prism->getCountry(),
        ];
        if ($one) {
            return DB::connect()->table(self::TABLE)
                ->where(fn(Where $w) => $w->equalTo('openid', $prism->getOpenid()))
                ->update($data);
        } else {
            $data['open_id'] = $prism->getOpenid();
            return DB::connect()->table(self::TABLE)->insert($data);
        }
    }

}