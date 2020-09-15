<?php

namespace Yonna\QuickStart\Scope;

use Yonna\QuickStart\Mapping\User\UserStatus;
use Yonna\Database\DB;
use Yonna\Database\Driver\Pdo\Where;
use Yonna\QuickStart\Prism\UserPrism;
use Yonna\Throwable\Exception;
use Yonna\Validator\ArrayValidator;

class User extends AbstractScope
{

    const TABLE = 'user';

    /**
     * 获取详情
     * @return array
     * @throws Exception\DatabaseException
     * @throws Exception\ThrowException
     */
    public function one(): array
    {
        ArrayValidator::required($this->input(), ['id'], function ($error) {
            Exception::throw($error);
        });
        $result = DB::connect()->table('user')->field('id,status,inviter_user_id,register_time')
            ->where(fn(Where $w) => $w->equalTo('id', $this->input('id')))
            ->one();
        $result = $this->scope(UserMeta::class, 'attach', ['attach' => $result]);
        $result = $this->scope(UserAccount::class, 'attach', ['attach' => $result]);
        return $result;
    }

    /**
     * 获取列表
     * @return array
     * @throws Exception\DatabaseException
     */
    public function multi(): array
    {
        $prism = new UserPrism($this->request());

        $db = DB::connect()
            ->table('user')
            ->field('id,status,inviter_user_id,register_time')
            ->where(function (Where $w) use ($prism) {
                $w->notEqualTo('status', UserStatus::DELETE);
                $prism->getId() && $w->equalTo('id', $prism->getId());
                $prism->getIds() && $w->in('ids', $prism->getIds());
                $prism->getInviterUserId() && $w->equalTo('inviter_user_id', $prism->getInviterUserId());
                $prism->getStatus() && $w->equalTo('status', $prism->getStatus());
                $prism->getRegisterTime() && $w->between('register_time', $prism->getRegisterTime());
            });
        if ($prism->getOrderBy()) {
            $db->orderByStr($prism->getOrderBy());
        } else {
            $db->orderBy('id', 'desc', 'user');
        }
        $list = $db->multi() ?? [];
        $list = $this->scope(UserMeta::class, 'attach', ['attach' => $list]);
        $list = $this->scope(UserAccount::class, 'attach', ['attach' => $list]);
        return $list;
    }

    /**
     * 获取分页列表
     * @return array
     * @throws Exception\DatabaseException
     */
    public function page(): array
    {
        $prism = new UserPrism($this->request());
        $db = DB::connect()
            ->table('user')
            ->field('id,status,inviter_user_id,register_time')
            ->where(function (Where $w) use ($prism) {
                $w->notEqualTo('status', UserStatus::DELETE);
                $prism->getId() && $w->equalTo('id', $prism->getId());
                $prism->getIds() && $w->in('ids', $prism->getIds());
                $prism->getInviterUserId() && $w->equalTo('inviter_user_id', $prism->getInviterUserId());
                $prism->getStatus() && $w->equalTo('status', $prism->getStatus());
                $prism->getRegisterTime() && $w->between('register_time', $prism->getRegisterTime());
            });
        if ($prism->getOrderBy()) {
            $db->orderByStr($prism->getOrderBy());
        } else {
            $db->orderBy('id', 'desc', 'user');
        }
        $page = $db->page($prism->getCurrent(), $prism->getPer());
        $page = $this->scope(UserMeta::class, 'attach', ['attach' => $page]);
        $page = $this->scope(UserAccount::class, 'attach', ['attach' => $page]);
        return $page;
    }

    /**
     * @return int
     * @throws Exception\DatabaseException
     */
    public function insert()
    {
        ArrayValidator::required($this->input(), ['name'], function ($error) {
            Exception::throw($error);
        });
        $add = [
            'name' => $this->input('name'),
            'upper_id' => $this->input('upper_id') ?? 0,
            'status' => $this->input('status') ?? UserStatus::PENDING,
            'sort' => $this->input('sort') ?? 0,
        ];
        return DB::connect()->table(self::TABLE)->insert($add);
    }

    /**
     * @return int
     * @throws Exception\DatabaseException
     */
    public function update()
    {
        ArrayValidator::required($this->input(), ['id'], function ($error) {
            Exception::throw($error);
        });
        $data = [
            'name' => $this->input('name'),
            'upper_id' => $this->input('upper_id'),
            'status' => $this->input('status'),
            'sort' => $this->input('sort'),
        ];
        if ($data) {
            return DB::connect()->table(self::TABLE)
                ->where(fn(Where $w) => $w->equalTo('id', $this->input('id')))
                ->update($data);
        }
        return true;
    }

    /**
     * @return int
     * @throws Exception\DatabaseException
     */
    public function delete()
    {
        ArrayValidator::required($this->input(), ['id'], function ($error) {
            Exception::throw($error);
        });
        return DB::connect()->table(self::TABLE)
            ->where(fn(Where $w) => $w->equalTo('id', $this->input('id')))
            ->delete();
    }

    /**
     * @return int
     * @throws Exception\DatabaseException
     */
    public function multiStatus()
    {
        ArrayValidator::required($this->input(), ['ids', 'status'], function ($error) {
            Exception::throw($error);
        });
        return DB::connect()->table(self::TABLE)
            ->where(fn(Where $w) => $w->in('id', $this->input('ids')))
            ->update(["status" => $this->input('status')]);
    }

}