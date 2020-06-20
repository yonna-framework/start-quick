<?php

namespace Yonna\Services\Log;


class Prism extends \Yonna\IO\Prism
{

    protected int $current = 1;
    protected int $per = 10;
    protected $key = null;
    protected $type = null;
    protected $record_timestamp = null;

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
     * @return null
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @return null
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return null
     */
    public function getRecordTimestamp()
    {
        return $this->record_timestamp;
    }


}