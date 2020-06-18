<?php

namespace Yonna\QuickStart\Middleware;

use Yonna\IO\Request;
use Yonna\Middleware\Before;
use Yonna\Throwable\Exception;

class Sign extends Before
{

    /**
     * @return Request
     * @throws Exception\PermissionException
     * @throws Exception\ThrowException
     */
    public function handle(): Request
    {
        $isLogin = $this->scope(\App\Scope\User\Sign::class, 'isLogin');
        if ($isLogin !== true) {
            Exception::permission('UN_LOGIN');
        }
        return $this->request();
    }

}