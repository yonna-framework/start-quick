<?php

namespace Yonna\Throwable;


use Throwable;
use Yonna\Throwable\Exception\ThrowException;
use Yonna\Throwable\Exception\LogoutException;
use Yonna\Throwable\Exception\ErrorException;

use Yonna\Throwable\Exception\Error\DatabaseException;
use Yonna\Throwable\Exception\Error\ParamsException;

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
     * @throws ErrorException
     */
    public static function error($msg)
    {
        throw new ErrorException($msg);
    }

    /**
     * @param $msg
     * @throws LogoutException
     */
    public static function logout($msg)
    {
        throw new LogoutException($msg);
    }

    // ----------------ERROR---------------------

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

}