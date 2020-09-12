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

    /**
     * @param string $call
     * @param string $action
     * @param array $input
     * @return mixed
     * @throws Exception\ThrowException
     */
    public function scope(string $call, string $action, array $input = [])
    {
        $Scope = Core::get($call, $this->request(), $input);
        if (!$Scope instanceof Scope) {
            Exception::throw("Class {$call} is not instanceof Log");
        }
        return $Scope->$action();
    }

}