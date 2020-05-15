<?php

namespace Yonna\Throwable\Exception;


use Exception;

/**
 * 参数的错误
 * Class ParamsException
 * @package Yonna\Throwable\Exception
 */
class ParamsException extends Exception
{

    protected $code = Code::PARAMS;

}