<?php

namespace Yonna\Services\I18n;

/**
 * Class Result
 * @package Yonna\Services\I18n
 */
class Result
{

    private $result = [];

    public function push($res)
    {
        $this->result[] = $res;
    }

    public function get()
    {
        return $this->result;
    }

}