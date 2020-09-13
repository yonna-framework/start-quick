<?php

namespace Yonna\QuickStart;

use Yonna\I18n\I18n;
use Yonna\IO\Request;
use Yonna\Log\Log;
use Yonna\Log\Prism;
use Yonna\QuickStart\Middleware\Debug;
use Yonna\QuickStart\Middleware\Limiter;
use Yonna\QuickStart\Middleware\Logging;
use Yonna\QuickStart\Scope\DataHobby;
use Yonna\QuickStart\Scope\License;
use Yonna\QuickStart\Scope\DataSpeciality;
use Yonna\QuickStart\Scope\DataWork;
use Yonna\QuickStart\Scope\Essay;
use Yonna\QuickStart\Scope\EssayCategory;
use Yonna\QuickStart\Scope\SdkWxmp;
use Yonna\QuickStart\Scope\User;
use Yonna\QuickStart\Scope\UserMe;
use Yonna\QuickStart\Scope\UserLogin;
use Yonna\QuickStart\Scope\UserMetaCategory;
use Yonna\QuickStart\Scope\Stat;
use Yonna\QuickStart\Scope\Xoss;
use Yonna\Scope\Config;
use Yonna\Throwable\Exception\DatabaseException;
use Yonna\Throwable\Exception\ParamsException;

class Install
{

    public static function log(): void
    {
        Config::middleware([Logging::class],
            function () {
                Config::group(['log'], function () {
                    Config::post('catalog', function () {
                        return Log::file()->catalog();
                    });
                    Config::post('file', function (Request $request) {
                        return Log::file()->fileContent($request->getInput('file'));
                    });
                    Config::post('db', function (Request $request) {
                        return Log::db()->page(new Prism($request));
                    });
                });
            }
        );
    }

    /**
     * @throws DatabaseException
     * @throws ParamsException
     */
    public static function i18n(): void
    {
        Config::middleware([Debug::class],
            function () {
                Config::group(['i18n'], function () {
                    Config::post('init', function (Request $request) {
                        return (new I18n())->init();
                    });
                    Config::post('set', function (Request $request) {
                        $input = $request->getInput();
                        $data = [];
                        foreach (I18n::ALLOW_LANG as $lang) {
                            $data[$lang] = $input[$lang] ?? '';
                        }
                        return (new I18n())->set($input['unique_key'], $data);
                    });
                });
            }
        );
        Config::middleware([Logging::class], function () {
            Config::group(['i18n'], function () {
                Config::post('backup', function () {
                    return (new I18n())->backup();
                });
                Config::post('page', function (Request $request) {
                    $input = $request->getInput();
                    return (new I18n())->page(
                        $input['current'] ?? 1,
                        $input['per'] ?? 10,
                        [
                            'unique_key' => $input['unique_key'] ?? null,
                        ],
                    );
                });
            });
        });
        Config::group(['i18n'], function () {
            Config::post('all', function () {
                return (new I18n())->get();
            });
        });
    }

    public static function xoss(): void
    {
        Config::middleware([Limiter::class],
            function () {
                Config::group(['xoss'], function () {

                    Config::get('download', Xoss::class, 'download');

                    Config::middleware([Logging::class], function () {
                        Config::post('upload', Xoss::class, 'upload');
                    });
                });
            }
        );
    }

    public static function stat(): void
    {
        Config::middleware([Limiter::class, Logging::class],
            function () {
                Config::group(['stat'], function () {
                    Config::post('user', Stat::class, 'user');
                    Config::post('userAccount', Stat::class, 'account');
                });
            }
        );
    }

    public static function essay(): void
    {
        Config::middleware([Limiter::class],
            function () {
                Config::group(['essay'], function () {

                    Config::post('pic', Essay::class, 'pic');
                    Config::post('list', Essay::class, 'multi');
                    Config::post('views', Essay::class, 'views');
                    Config::post('likes', Essay::class, 'likes');

                    Config::middleware([Logging::class], function () {

                        Config::post('add', Essay::class, 'insert');
                        Config::post('edit', Essay::class, 'update');
                        Config::post('del', Essay::class, 'delete');
                        Config::post('mDel', Essay::class, 'multiDelete');
                        Config::post('mStatus', Essay::class, 'multiStatus');
                        Config::post('info', Essay::class, 'one');
                        Config::post('page', Essay::class, 'page');

                        Config::group(['category'], function () {
                            Config::post('add', EssayCategory::class, 'insert');
                            Config::post('edit', EssayCategory::class, 'update');
                            Config::post('del', EssayCategory::class, 'delete');
                            Config::post('mDel', EssayCategory::class, 'multiDelete');
                            Config::post('mStatus', EssayCategory::class, 'multiStatus');
                            Config::post('info', EssayCategory::class, 'one');
                            Config::post('list', EssayCategory::class, 'multi');
                            Config::post('page', EssayCategory::class, 'page');
                        });

                    });
                });
            }
        );
    }

    public static function sdk(): void
    {
        Config::middleware([Limiter::class],
            function () {
                Config::group(['sdk'], function () {

                    Config::group(['wxmp'], function () {
                        Config::post('login', SdkWxmp::class, 'in');
                    });

                });
            }
        );
    }

    public static function license(): void
    {
        Config::middleware([Limiter::class, Logging::class],
            function () {
                Config::group(['license'], function () {
                    Config::post('tree', License::class, 'tree');
                    Config::post('info', License::class, 'one');
                    Config::post('add', License::class, 'insert');
                    Config::post('edit', License::class, 'update');
                    Config::post('del', License::class, 'delete');
                });
            }
        );
    }

    public static function user(): void
    {
        Config::middleware([Limiter::class],
            function () {
                Config::group(['user'], function () {
                    Config::post('login', UserLogin::class, 'in');
                    Config::post('logging', UserLogin::class, 'isLogging');
                    Config::post('logout', UserLogin::class, 'out');
                });
                Config::middleware([Logging::class], function () {
                    Config::group(['me'], function () {
                        Config::post('info', UserMe::class, 'one');
                        Config::post('edit', UserMe::class, 'update');
                    });
                });
            }
        );
    }

    public static function userMetaCategory(): void
    {
        Config::middleware([Limiter::class, Logging::class],
            function () {
                Config::group(['user', 'meta', 'category'], function () {
                    Config::post('info', UserMetaCategory::class, 'one');
                    Config::post('list', UserMetaCategory::class, 'multi');
                    Config::post('add', UserMetaCategory::class, 'insert');
                    Config::post('edit', UserMetaCategory::class, 'update');
                    Config::post('del', UserMetaCategory::class, 'delete');
                    Config::post('mStatus', UserMetaCategory::class, 'multiStatus');
                });
            }
        );
    }

    public static function data(): void
    {
        Config::middleware([Limiter::class, Logging::class],
            function () {
                Config::group(['data', 'hobby'], function () {
                    Config::post('info', DataHobby::class, 'one');
                    Config::post('list', DataHobby::class, 'multi');
                    Config::post('page', DataHobby::class, 'page');
                    Config::post('add', DataHobby::class, 'insert');
                    Config::post('edit', DataHobby::class, 'update');
                    Config::post('del', DataHobby::class, 'delete');
                    Config::post('mDel', DataHobby::class, 'multiDelete');
                    Config::post('mStatus', DataHobby::class, 'multiStatus');
                });
                Config::group(['data', 'speciality'], function () {
                    Config::post('info', DataSpeciality::class, 'one');
                    Config::post('list', DataSpeciality::class, 'multi');
                    Config::post('page', DataSpeciality::class, 'page');
                    Config::post('add', DataSpeciality::class, 'insert');
                    Config::post('edit', DataSpeciality::class, 'update');
                    Config::post('del', DataSpeciality::class, 'delete');
                    Config::post('mDel', DataSpeciality::class, 'multiDelete');
                    Config::post('mStatus', DataSpeciality::class, 'multiStatus');
                });
                Config::group(['data', 'work'], function () {
                    Config::post('info', DataWork::class, 'one');
                    Config::post('list', DataWork::class, 'multi');
                    Config::post('page', DataWork::class, 'page');
                    Config::post('add', DataWork::class, 'insert');
                    Config::post('edit', DataWork::class, 'update');
                    Config::post('del', DataWork::class, 'delete');
                    Config::post('mDel', DataWork::class, 'multiDelete');
                    Config::post('mStatus', DataWork::class, 'multiStatus');
                });
            }
        );
    }

}