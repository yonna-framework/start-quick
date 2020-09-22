<?php

namespace Yonna\Throwable\Exception\Error;


use Yonna\Throwable\Exception\Code;
use Yonna\Throwable\Exception\ErrorException;

/**
 * 错误[数据库]
 * Class DatabaseException
 * @package Yonna\Throwable\Exception
 */
class DatabaseException extends ErrorException
{

    protected $code = Code::ERROR_DATABASE;

}