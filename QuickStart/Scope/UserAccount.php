<?php

namespace Yonna\QuickStart\Scope;

use Yonna\Foundation\Arr;
use Yonna\QuickStart\Prism\UserAccountPrism;
use Yonna\Database\DB;
use Yonna\Database\Driver\Pdo\Where;
use Yonna\Throwable\Exception;

/**
 * Class Meta
 * @package Yonna\QuickStart\Scope\User
 */
class UserAccount extends AbstractScope
{

    const TABLE = 'user_account';

    /**
     * @return array
     * @throws Exception\DatabaseException
     */
    public function attach(): array
    {
        $prism = new UserAccountPrism($this->request());
        $data = $prism->getAttach();
        if (!$data) {
            return [];
        }
        $isPage = isset($data['page']);
        $isOne = Arr::isAssoc($data);
        if ($isPage) {
            $tmp = $data['list'];
        } elseif ($isOne) {
            $tmp = [$data];
        } else {
            $tmp = $data;
        }
        $ids = array_column($tmp, 'user_id');
        $values = DB::connect()->table(self::TABLE)
            ->where(fn(Where $w) => $w->in('user_id', $ids))
            ->multi();
        $accounts = [];
        foreach ($values as $v) {
            if (!isset($accounts[$v['user_account_user_id']])) {
                $accounts[$v['user_account_user_id']] = [];
            }
            $accounts[$v['user_account_user_id']][] = $v;
        }
        unset($values);
        foreach ($tmp as $uk => $u) {
            $tmp[$uk]['user_account'] = empty($accounts[$u['user_id']]) ? [] : $accounts[$u['user_id']];
        }
        if ($isPage) {
            $data['list'] = $tmp;
            return $data;
        }
        return $isOne ? $tmp[0] : $tmp;
    }

}