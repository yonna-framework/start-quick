<?php

namespace Yonna\Throwable;

class Error
{

    /**
     * @param $msg
     */
    public static function throw($msg)
    {
        trigger_error($msg, E_USER_ERROR);
    }

    /**
     * @param $msg
     */
    public static function warning($msg)
    {
        trigger_error($msg, E_USER_WARNING);
    }

    /**
     * @param $msg
     */
    public static function notice($msg)
    {
        trigger_error($msg, E_USER_NOTICE);
    }

    /**
     * @param $msg
     */
    public static function deprecated($msg)
    {
        trigger_error($msg, E_USER_DEPRECATED);
    }

}