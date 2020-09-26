<?php

namespace Yonna\QuickStart\Prism;


use Yonna\IO\Prism;

class LeagueMemberPrism extends Prism
{

    protected int $current = 1;
    protected int $per = 10;
    protected ?string $order_by = null;
    protected ?int $league_id = null;
    protected ?int $user_id = null;
    protected ?int $permission = null;
    protected ?int $status = null;
    protected ?string $apply_reason = null;
    protected ?string $rejection_reason = null;
    protected ?string $passed_reason = null;
    protected ?string $delete_reason = null;
    protected ?int $apply_time = null;
    protected ?int $rejection_time = null;
    protected ?int $pass_time = null;
    protected ?int $delete_time = null;

    protected ?string $reason = null;

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
    public function getLeagueId(): ?int
    {
        return $this->league_id;
    }

    /**
     * @param int|null $league_id
     */
    public function setLeagueId(?int $league_id): void
    {
        $this->league_id = $league_id;
    }

    /**
     * @return int|null
     */
    public function getUserId(): ?int
    {
        return $this->user_id;
    }

    /**
     * @param int|null $user_id
     */
    public function setUserId(?int $user_id): void
    {
        $this->user_id = $user_id;
    }

    /**
     * @return int|null
     */
    public function getPermission(): ?int
    {
        return $this->permission;
    }

    /**
     * @param int|null $permission
     */
    public function setPermission(?int $permission): void
    {
        $this->permission = $permission;
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
     * @return string|null
     */
    public function getApplyReason(): ?string
    {
        return $this->apply_reason;
    }

    /**
     * @param string|null $apply_reason
     */
    public function setApplyReason(?string $apply_reason): void
    {
        $this->apply_reason = $apply_reason;
    }

    /**
     * @return string|null
     */
    public function getRejectionReason(): ?string
    {
        return $this->rejection_reason;
    }

    /**
     * @param string|null $rejection_reason
     */
    public function setRejectionReason(?string $rejection_reason): void
    {
        $this->rejection_reason = $rejection_reason;
    }

    /**
     * @return string|null
     */
    public function getPassedReason(): ?string
    {
        return $this->passed_reason;
    }

    /**
     * @param string|null $passed_reason
     */
    public function setPassedReason(?string $passed_reason): void
    {
        $this->passed_reason = $passed_reason;
    }

    /**
     * @return string|null
     */
    public function getDeleteReason(): ?string
    {
        return $this->delete_reason;
    }

    /**
     * @param string|null $delete_reason
     */
    public function setDeleteReason(?string $delete_reason): void
    {
        $this->delete_reason = $delete_reason;
    }

    /**
     * @return int|null
     */
    public function getApplyTime(): ?int
    {
        return $this->apply_time;
    }

    /**
     * @param int|null $apply_time
     */
    public function setApplyTime(?int $apply_time): void
    {
        $this->apply_time = $apply_time;
    }

    /**
     * @return int|null
     */
    public function getRejectionTime(): ?int
    {
        return $this->rejection_time;
    }

    /**
     * @param int|null $rejection_time
     */
    public function setRejectionTime(?int $rejection_time): void
    {
        $this->rejection_time = $rejection_time;
    }

    /**
     * @return int|null
     */
    public function getPassTime(): ?int
    {
        return $this->pass_time;
    }

    /**
     * @param int|null $pass_time
     */
    public function setPassTime(?int $pass_time): void
    {
        $this->pass_time = $pass_time;
    }

    /**
     * @return int|null
     */
    public function getDeleteTime(): ?int
    {
        return $this->delete_time;
    }

    /**
     * @param int|null $delete_time
     */
    public function setDeleteTime(?int $delete_time): void
    {
        $this->delete_time = $delete_time;
    }

    /**
     * @return string|null
     */
    public function getReason(): ?string
    {
        return $this->reason;
    }

    /**
     * @param string|null $reason
     */
    public function setReason(?string $reason): void
    {
        $this->reason = $reason;
    }

}