<?php

namespace Yonna\Event;

/**
 * Class Event
 * @package Yonna\Event
 */
abstract class Event
{

    private $params = null;
    private $listeners = [];

    public function __construct($params)
    {
        $this->params = $params;
    }

    /**
     * 获取参数
     * @return null
     */
    public function getParams(){
        return $this->params;
    }

    /**
     * 设定listeners
     * @param array $listeners
     */
    public function listener(array $listeners)
    {
        $this->listeners = $listeners;
        foreach ($this->listeners as $l) {
            /**
             * @var Listener $lis
             */
            $lis = new $l($this);
            $lis->handle();
        }
    }

}