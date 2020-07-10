<?php

namespace Yonna\Throwable;


use Throwable;
use Yonna\Throwable\Exception\DatabaseException;
use Yonna\Throwable\Exception\DebugException;
use Yonna\Throwable\Exception\NotLoggingException;
use Yonna\Throwable\Exception\ParamsException;
use Yonna\Throwable\Exception\PermissionException;
use Yonna\Throwable\Exception\SDKException;
use Yonna\Throwable\Exception\ThrowException;

class Exception
{

    /**
     * @param Throwable $e
     * @throws Throwable
     */
    public static function origin(Throwable $e)
    {
        throw $e;
    }

    /**
     * @param $msg
     * @throws ThrowException
     */
    public static function throw($msg)
    {
        throw new ThrowException($msg);
    }

    /**
     * @param $msg
     * @throws DebugException
     */
    public static function debug($msg)
    {
        throw new DebugException($msg);
    }

    /**
     * @param $msg
     * @throws DatabaseException
     */
    public static function database($msg)
    {
        throw new DatabaseException($msg);
    }

    /**
     * @param $msg
     * @throws ParamsException
     */
    public static function params($msg)
    {
        throw new ParamsException($msg);
    }

    /**
     * @param $msg
     * @throws SDKException
     */
    public static function sdk($msg)
    {
        throw new SDKException($msg);
    }

    /**
     * @param $msg
     * @throws PermissionException
     */
    public static function permission($msg)
    {
        throw new PermissionException($msg);
    }

    /**
     * @param $msg
     * @throws NotLoggingException
     */
    public static function notLogging($msg)
    {
        throw new NotLoggingException($msg);
    }

}