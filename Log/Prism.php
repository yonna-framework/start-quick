<?php

namespace Yonna\Log;


class Prism extends \Yonna\IO\Prism
{

    protected int $current = 1;
    protected int $per = 10;
    protected ?string $key = null;
    protected ?string $type = null;
    protected ?int $record_time = null;

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
     * @return string|null
     */
    public function getKey(): ?string
    {
        return $this->key;
    }

    /**
     * @return string|null
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * @return int|null
     */
    public function getRecordTime(): ?int
    {
        return $this->record_time;
    }

}