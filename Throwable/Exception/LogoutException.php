<?php

namespace Yonna\Throwable\Exception;


use Exception;

/**
 * 未登录
 * Class NotLoggingException
 * @package Yonna\Throwable\Exception
 */
class LogoutException extends Exception
{

    protected $code = Code::LOGOUT;

}