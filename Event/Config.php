<?php

namespace Yonna\Event;

use Exception;
use Yonna\Foundation\System;

class Config
{

    protected static $stack = array();

    /**
     * @return array
     */
    public static function fetch(): array
    {
        return static::$stack;
    }

    /**
     * @param $key
     * @param null $default
     * @return array|bool|false|string|null
     */
    public static function env($key, $default = null)
    {
        return System::env($key, $default);
    }

    /**
     * 注册触发器,设定需要的参数要求
     * @param string $eventClass
     * @param array $listenerClasses
     * @throws Exception
     */
    public static function reg(string $eventClass, array $listenerClasses)
    {
        if (empty($eventClass)) throw new Exception('not event');
        if (empty($listenerClasses)) throw new Exception('not listener');
        if (!empty(self::$stack[$eventClass])) {
            throw new Exception("Event {$eventClass} already exist");
        }
        self::$stack[$eventClass] = $listenerClasses;
    }

    /**
     * 删除触发器
     * @param string $eventClass
     * @throws Exception
     */
    public static function del(string $eventClass)
    {
        if (empty($eventClass)) throw new Exception('not event');
        if (isset(self::$stack[$eventClass])) {
            unset(self::$stack[$eventClass]);
        }
    }

    /**
     * 触发触发器
     * @param string $eventClass
     * @param $params
     * @throws Exception
     */
    public static function act(string $eventClass, $params)
    {
        if (empty($eventClass)) throw new Exception('not event');
        if (empty(self::$stack[$eventClass])) {
            return;
        }
        /**
         * @var Event $event
         */
        $event = new $eventClass($params);
        $event->listener(self::$stack[$eventClass]);
    }

}