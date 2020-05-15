<?php

namespace Yonna\Throwable\Exception;


use Exception;

/**
 * debug引起的错误
 * Class DebugException
 * @package Yonna\Throwable\Exception
 */
class DebugException extends Exception
{

    protected $code = Code::DEBUG;

}