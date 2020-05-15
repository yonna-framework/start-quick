<?php

namespace Yonna\Throwable\Exception;


use Exception;

/**
 * 默认错误
 * Class DatabaseException
 * @package Yonna\Throwable\Exception
 */
class ThrowException extends Exception
{

    protected $code = Code::THROW;

}