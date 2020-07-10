<?php

namespace Yonna\QuickStart\Scope\Xoss;

use Yonna\QuickStart\Helper\Password;
use Yonna\QuickStart\Mapping\Common\Boolean;
use Yonna\QuickStart\Mapping\User\AccountType;
use Yonna\QuickStart\Mapping\User\UserStatus;
use Yonna\QuickStart\Scope\AbstractScope;
use Throwable;
use Yonna\Database\DB;
use Yonna\Database\Driver\Pdo\Where;
use Yonna\Log\Log;
use Yonna\Throwable\Exception;

/**
 * Class Xoss
 * @package Yonna\QuickStart\Scope\User
 */
class Xoss extends AbstractScope
{

    public function upload()
    {
        return true;
    }

}