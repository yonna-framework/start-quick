<?php

namespace Yonna\Throwable\Exception;


use Exception;

/**
 * Class ErrorException
 * @package Yonna\Throwable\Exception
 */
class ErrorException extends Exception
{

    protected $code = Code::ERROR;

}