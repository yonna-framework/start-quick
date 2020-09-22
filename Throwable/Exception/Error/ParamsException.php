<?php


namespace Yonna\Throwable\Exception\Error;


use Yonna\Throwable\Exception\Code;
use Yonna\Throwable\Exception\ErrorException;

/**
 * 错误[参数]
 * Class ParamsException
 * @package Yonna\Throwable\Exception
 */
class ParamsException extends ErrorException
{

    protected $code = Code::ERROR_PARAMS;

}