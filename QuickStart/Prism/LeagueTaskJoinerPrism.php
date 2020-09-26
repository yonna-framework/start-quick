<?php

namespace Yonna\QuickStart\Prism;


use Yonna\IO\Prism;

class LeagueTaskJoinerPrism extends Prism
{

    protected int $current = 1;
    protected int $per = 10;
    protected ?string $order_by = null;
    protected ?int $id = null;
    protected ?array $ids = null;

    protected ?int $task_id = null;
    protected ?int $user_id = null;
    protected ?int $league_id = null;
    protected ?int $status = null;
    protected ?string $abort_reason = null;
    protected ?string $give_up_reason = null;
    protected ?float $self_evaluation = null;
    protected ?float $league_evaluation = null;

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
    public function getTaskId(): ?int
    {
        return $this->task_id;
    }

    /**
     * @param int|null $task_id
     */
    public function setTaskId(?int $task_id): void
    {
        $this->task_id = $task_id;
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
    public function getAbortReason(): ?string
    {
        return $this->abort_reason;
    }

    /**
     * @param string|null $abort_reason
     */
    public function setAbortReason(?string $abort_reason): void
    {
        $this->abort_reason = $abort_reason;
    }

    /**
     * @return string|null
     */
    public function getGiveUpReason(): ?string
    {
        return $this->give_up_reason;
    }

    /**
     * @param string|null $give_up_reason
     */
    public function setGiveUpReason(?string $give_up_reason): void
    {
        $this->give_up_reason = $give_up_reason;
    }

    /**
     * @return float|null
     */
    public function getSelfEvaluation(): ?float
    {
        return $this->self_evaluation;
    }

    /**
     * @param float|null $self_evaluation
     */
    public function setSelfEvaluation(?float $self_evaluation): void
    {
        $this->self_evaluation = $self_evaluation;
    }

    /**
     * @return float|null
     */
    public function getLeagueEvaluation(): ?float
    {
        return $this->league_evaluation;
    }

    /**
     * @param float|null $league_evaluation
     */
    public function setLeagueEvaluation(?float $league_evaluation): void
    {
        $this->league_evaluation = $league_evaluation;
    }

}