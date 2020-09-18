<?php

namespace Yonna\QuickStart\Prism;


use Yonna\IO\Prism;

class LeagueAssociateDataPrism extends Prism
{

    protected int $current = 1;
    protected int $per = 10;
    protected ?int $league_id = null;
    protected ?string $data_id = null;

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
     * @return string|null
     */
    public function getDataId(): ?string
    {
        return $this->data_id;
    }

    /**
     * @param string|null $data_id
     */
    public function setDataId(?string $data_id): void
    {
        $this->data_id = $data_id;
    }

}