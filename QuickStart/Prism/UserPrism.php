<?php

namespace Yonna\QuickStart\Prism;


use Yonna\IO\Prism;

class UserPrism extends Prism
{

    protected int $current = 1;
    protected int $per = 10;
    protected ?string $order_by = null;
    protected ?int $id = null;
    protected ?array $ids = null;
    protected ?int $status = null;
    protected ?int $inviter_user_id = null;
    protected array $register_time = [];

    /**
     * @return int
     */
    public function getCurrent(): int
    {
        return $this->current;
    }

    /**
     * @param int $current
     */
    public function setCurrent(int $current): void
    {
        $this->current = $current;
    }

    /**
     * @return int
     */
    public function getPer(): int
    {
        return $this->per;
    }

    /**
     * @param int $per
     */
    public function setPer(int $per): void
    {
        $this->per = $per;
    }

    /**
     * @return string|null
     */
    public function getOrderBy(): ?string
    {
        return $this->order_by;
    }

    /**
     * @param string|null $order_by
     */
    public function setOrderBy(?string $order_by): void
    {
        $this->order_by = $order_by;
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int|null $id
     */
    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return array|null
     */
    public function getIds(): ?array
    {
        return $this->ids;
    }

    /**
     * @param array|null $ids
     */
    public function setIds(?array $ids): void
    {
        $this->ids = $ids;
    }

    /**
     * @return int|null
     */
    public function getStatus(): ?int
    {
        return $this->status;
    }

    /**
     * @param int|null $status
     */
    public function setStatus(?int $status): void
    {
        $this->status = $status;
    }

    /**
     * @return int|null
     */
    public function getInviterUserId(): ?int
    {
        return $this->inviter_user_id;
    }

    /**
     * @param int|null $inviter_user_id
     */
    public function setInviterUserId(?int $inviter_user_id): void
    {
        $this->inviter_user_id = $inviter_user_id;
    }

    /**
     * @return array
     */
    public function getRegisterTime(): array
    {
        return $this->register_time;
    }

    /**
     * @param array $register_time
     */
    public function setRegisterTime(array $register_time): void
    {
        $this->register_time = $register_time;
    }

}