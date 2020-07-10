<?php

namespace Yonna\QuickStart\Middleware;

use Yonna\IO\Request;
use Yonna\Middleware\Before;
use Yonna\QuickStart\Scope\User\Login;
use Yonna\Throwable\Exception;

class Logging extends Before
{

    /**
     * @return Request
     * @throws Exception\NotLoggingException
     * @throws Exception\ThrowException
     */
    public function handle(): Request
    {
        $isLogin = $this->scope(Login::class, 'isLogging');
        if ($isLogin !== true) {
            Exception::notLogging('UN_LOGIN');
        }
        $request = $this->request();
        $request->setLoggingId($this->scope(Login::class, 'getLoggingId'));
        return $request;
    }
}