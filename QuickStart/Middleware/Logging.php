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
     * @throws Exception\PermissionException
     * @throws Exception\ThrowException
     */
    public function handle(): Request
    {
        $isLogin = $this->scope(Login::class, 'isLogging');
        if ($isLogin !== true) {
            Exception::permission('UN_LOGIN');
        }
        return $this->request();
    }

}