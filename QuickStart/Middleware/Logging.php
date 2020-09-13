<?php

namespace Yonna\QuickStart\Middleware;

use Yonna\IO\Request;
use Yonna\Middleware\Before;
use Yonna\QuickStart\Scope\UserLogin;
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
        $isLogin = $this->scope(UserLogin::class, 'isLogging');
        if ($isLogin !== true) {
            Exception::notLogging('UN_LOGIN');
        }
        $request = $this->request();
        $request->setLoggingId($this->scope(UserLogin::class, 'getLoggingId'));
        return $request;
    }
}