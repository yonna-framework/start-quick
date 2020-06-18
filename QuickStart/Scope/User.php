<?php


use Yonna\Log\DatabaseLog;
use Yonna\QuickStart\Config as QuickStartConfig;
use Yonna\QuickStart\Middleware\Limiter;
use Yonna\QuickStart\Middleware\Sign;
use Yonna\Scope\Config;
use App\Scope\User\Fetch;
use App\Scope\User\Modify;

//use App\Scope\User\Sign;

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
        Config::middleware(
            [
                Limiter::class,
            ],
            function () {
                Config::group(['user', 'login'], function () {

                    Config::post('admin', Sign::class, 'in');

                });
            }
        );

        Config::group(['system', 'data'], function () {

            Config::post('getInfoByKey', Sign::class, 'infoByKey');

        });

        Config::group(['project'], function () {
            Config::post('stat', \App\Scope\Project\Fetch::class, 'stat');
        });

        Config::middleware( // check auth
            [
                Sign::class,
            ],
            function () {

                Config::group(['user', 'change'], function () {

                });
                Config::group('user', function () {

                    Config::post('logout', Sign::class, 'out');

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
