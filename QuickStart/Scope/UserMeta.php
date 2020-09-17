<?php

namespace Yonna\QuickStart\Scope;

use Yonna\Foundation\Arr;
use Yonna\QuickStart\Mapping\Common\Boolean;
use Yonna\QuickStart\Mapping\User\MetaValueFormat;
use Yonna\QuickStart\Prism\UserMetaPrism;
use Yonna\Database\DB;
use Yonna\Database\Driver\Pdo\Where;
use Yonna\Throwable\Exception;
use Yonna\Validator\ArrayValidator;

/**
 * Class Meta
 * @package Yonna\QuickStart\Scope\User
 */
class UserMeta extends AbstractScope
{

    const TABLE = 'user_meta';

    /**
     * @return mixed
     * @throws Exception\DatabaseException
     */
    public function one(): array
    {
        ArrayValidator::required($this->input(), ['id'], function ($error) {
            Exception::throw($error);
        });
        return DB::connect()->table(self::TABLE)
            ->where(fn(Where $w) => $w->equalTo('id', $this->input('id')))
            ->one();
    }

    /**
     * @return mixed
     * @throws Exception\DatabaseException
     */
    public function multi(): array
    {
        $prism = new UserMetaPrism($this->request());
        return DB::connect()->table(self::TABLE)
            ->where(function (Where $w) use ($prism) {
                $prism->getUserId() && $w->equalTo('user_id', $prism->getUserId());
                $prism->getKey() && $w->equalTo('key', $prism->getKey());
            })
            ->multi();
    }

    /**
     * @return array
     * @throws Exception\ThrowException
     * @throws Exception\DatabaseException
     */
    public function attach(): array
    {
        $prism = new UserMetaPrism($this->request());
        $data = $prism->getAttach();
        $isPage = isset($data['page']);
        $isOne = Arr::isAssoc($data);
        if ($isPage) {
            $tmp = $data['list'];
        } elseif ($isOne) {
            $tmp = [$data];
        } else {
            $tmp = $data;
        }
        if (!$tmp) {
            return [];
        }
        $ids = array_column($tmp, 'user_id');
        $category = $this->scope(UserMetaCategory::class, 'multi', ['status' => Boolean::true]);
        $values = DB::connect()->table(self::TABLE)
            ->where(fn(Where $w) => $w->in('user_id', $ids))
            ->multi();
        $meta = [];
        foreach ($values as $v) {
            if (!isset($meta[$v['user_meta_user_id']])) {
                $meta[$v['user_meta_user_id']] = [];
            }
            $meta[$v['user_meta_user_id']][$v['user_meta_user_key']] = $v['user_meta_user_value'];
        }
        unset($values);
        foreach ($category as $c) {
            $key = $c['user_meta_category_key'];
            foreach ($tmp as $uk => $u) {
                if (!empty($meta[$u['user_id']]) && !empty($meta[$u['user_id']][$key])) {
                    $val = $meta[$u['user_id']][$key];
                } else {
                    $val = $c['user_meta_category_value_default'];
                }
                $tmp[$uk]['user_meta_' . $key] = UserMetaCategory::valueFormat($val, $c['user_meta_category_value_format']);
            }
        }
        if ($isPage) {
            $data['list'] = $tmp;
            return $data;
        }
        return $isOne ? $tmp[0] : $tmp;
    }

}