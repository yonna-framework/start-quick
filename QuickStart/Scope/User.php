<?php


use Yonna\Log\DatabaseLog;
use Yonna\Services\User\Fetch;
use Yonna\Services\User\Modify;
use Yonna\Services\User\Sign;
use Yonna\QuickStart\Config as QuickStartConfig;
use Yonna\QuickStart\Middleware\Limiter;
use Yonna\QuickStart\Middleware\Logging;
use Yonna\Scope\Config;

class User
{

    /**
     * I18n constructor.
     */
    public function __construct()
    {
        $file = QuickStartConfig::getAppRoot() . '/user';
        if (!is_file($file)) {
            file_put_contents($file, '');
            (new DatabaseLog())->initDatabase();
        }
    }

    public function install()
    {

        Config::middleware([Limiter::class],
            function () {
                Config::group(['user', 'admin'], function () {
                    Config::post('login', Sign::class, 'in');
                });
            }
        );

        Config::middleware( // check auth
            [
                Logging::class,
            ],
            function () {

                Config::group(['user', 'change'], function () {

                });
                Config::group('user', function () {

                    Config::post('logout', Logging::class, 'out');

                    Config::post('list', Fetch::class, 'list');
                    Config::post('info', Fetch::class, 'info');

                    Config::group('change', function () {
                        Config::post('loginName', Modify::class, 'changeLoginName');
                        Config::post('password', Modify::class, 'changePassword');
                    });

                });

            }
        );
    }
}
