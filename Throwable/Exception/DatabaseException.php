<?php

namespace Yonna\Throwable\Exception;

/**
 * 错误[数据库]
 * Class DatabaseException
 * @package Yonna\Throwable\Exception
 */
class DatabaseException extends ThrowException
{

    protected $code = Code::ERROR_DATABASE;

}