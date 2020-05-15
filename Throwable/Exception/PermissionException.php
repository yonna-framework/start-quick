<?php

namespace Yonna\Throwable\Exception;


use Exception;

/**
 * 权限的错误
 * Class PermissionException
 * @package Yonna\Throwable\Exception
 */
class PermissionException extends Exception
{

    protected $code = Code::PERMISSION;

}