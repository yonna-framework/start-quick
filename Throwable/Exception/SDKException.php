<?php

namespace Yonna\Throwable\Exception;


use Exception;

/**
 * SDK的错误
 * Class SDKException
 * @package Yonna\Throwable\Exception
 */
class SDKException extends Exception
{

    protected $code = Code::SDK;

}