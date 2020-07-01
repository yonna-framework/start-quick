<?php

namespace Yonna\QuickStart\Scope\User;


class Prism extends \Yonna\IO\Prism
{

    protected int $current = 1;
    protected int $per = 10;

    protected $id; // bigint unsigned NOT NULL用户id
    protected $status; //tinyint NOT NULL状态[-10注销,-3冻结,-2审核驳回,1待审核,2审核通过]
    protected $password; //char(255) NOT NULL登录密码，不一定有，如通过微信登录的就没有
    protected $inviter_user_id; //bigint NOT NULL邀请用户id[user_id]
    protected $register_datetime; //datetime NOT NULL注册时间

    /**
     * @return int
     */
    public function getCurrent(): int
    {
        return $this->current;
    }

    /**
     * @return int
     */
    public function getPer(): int
    {
        return $this->per;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id): void
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param mixed $status
     */
    public function setStatus($status): void
    {
        $this->status = $status;
    }

    /**
     * @return mixed
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param mixed $password
     */
    public function setPassword($password): void
    {
        $this->password = $password;
    }

    /**
     * @return mixed
     */
    public function getInviterUserId()
    {
        return $this->inviter_user_id;
    }

    /**
     * @param mixed $inviter_user_id
     */
    public function setInviterUserId($inviter_user_id): void
    {
        $this->inviter_user_id = $inviter_user_id;
    }

    /**
     * @return mixed
     */
    public function getRegisterDatetime()
    {
        return $this->register_datetime;
    }

    /**
     * @param mixed $register_datetime
     */
    public function setRegisterDatetime($register_datetime): void
    {
        $this->register_datetime = $register_datetime;
    }

}