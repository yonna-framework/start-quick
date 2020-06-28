<?php

namespace Yonna\Throwable\Exception;


use Exception;

/**
 * 提示
 * Class TipsException
 * @package Yonna\Throwable\Exception
 */
class TipsException extends Exception
{

    protected $code = Code::TIPS;

}