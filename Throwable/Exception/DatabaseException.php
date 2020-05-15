<?php

namespace Yonna\Throwable\Exception;


use Exception;

/**
 * 数据库的错误
 * Class DatabaseException
 * @package Yonna\Throwable\Exception
 */
class DatabaseException extends Exception
{

    protected $code = Code::DATABASE;

}