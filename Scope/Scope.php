<?php

namespace Yonna\Scope;

use Yonna\Core;
use Yonna\Throwable\Exception;

/**
 * Class Middleware
 * @package Core\Core\Log
 */
abstract class Scope extends Kernel
{

    public function __construct(object $request)
    {
        parent::__construct($request);
    }

    /**
     * @param string $call
     * @param string $action
     * @return mixed
     * @throws Exception\ThrowException
     */
    public function scope(string $call, string $action)
    {
        $Scope = Core::get($call, $this->request());
        if (!$Scope instanceof Scope) {
            Exception::throw("Class {$call} is not instanceof Log");
        }
        return $Scope->$action();
    }

}