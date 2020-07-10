<?php

namespace Yonna\Throwable\Exception;


use Exception;

/**
 * 未登录
 * Class NotLoggingException
 * @package Yonna\Throwable\Exception
 */
class NotLoggingException extends Exception
{

    protected $code = Code::NOT_LOGGING;

}