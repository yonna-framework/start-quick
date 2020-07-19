<?php

namespace Yonna\QuickStart\Middleware;

use Yonna\Database\DB;
use Yonna\IO\Request;
use Yonna\Middleware\Before;
use Yonna\Throwable\Exception;

class Limiter extends Before
{

    const REDIS_KEY = 'limiter:';
    const TIMEOUT = 3;
    const BAN = 60 * 10; // 10分钟封号
    const LIMIT = 30;

    /**
     * IP N秒内请求限制器
     * @return Request
     * @throws Exception\DatabaseException
     * @throws Exception\PermissionException
     */
    public function handle(): Request
    {
        $ip = $this->request()->getIp();
        $k = self::REDIS_KEY . $ip;
        $limit = DB::redis()->gcr($k);
        if ($limit > self::LIMIT) {
            DB::redis()->expire($k, self::BAN);
            Exception::permission('OVER LIMIT');
        }
        DB::redis()->incr($k);
        DB::redis()->expire($k, self::TIMEOUT);
        return $this->request();
    }

}