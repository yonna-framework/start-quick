<?php

namespace Yonna\QuickStart\Middleware;

use Yonna\IO\Request;
use Yonna\Middleware\Before;
use Yonna\Throwable\Exception;

class Debug extends Before
{
    /**
     * @return Request
     * @throws Exception\DebugException
     */
    public function handle(): Request
    {
        if (getenv('IS_DEBUG') === 'false') {
            Exception::debug('NOT_DEBUG');
        }
        return $this->request();
    }

}