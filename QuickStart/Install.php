<?php

namespace Yonna\QuickStart;

use Yonna\I18n\I18n;
use Yonna\IO\Request;
use Yonna\Log\Log;
use Yonna\Log\Prism;
use Yonna\QuickStart\Middleware\Debug;
use Yonna\QuickStart\Middleware\Limiter;
use Yonna\QuickStart\Middleware\Logging;
use Yonna\QuickStart\Scope\Essay\Essay;
use Yonna\QuickStart\Scope\Essay\EssayCategory;
use Yonna\QuickStart\Scope\Sdk\Wxmp;
use Yonna\QuickStart\Scope\User\AdminFetch;
use Yonna\QuickStart\Scope\User\Login;
use Yonna\QuickStart\Scope\User\Meta;
use Yonna\QuickStart\Scope\User\Stat;
use Yonna\QuickStart\Scope\Xoss\Xoss;
use Yonna\Scope\Config;

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

    public static function user(): void
    {
        Config::middleware([Limiter::class],
            function () {
                Config::group(['user'], function () {
                    Config::post('logging', Login::class, 'isLogging');
                    Config::post('logout', Login::class, 'out');
                    Config::middleware([Logging::class], function () {
                        Config::group(['meta'], function () {
                            Config::post('category', Meta::class, 'category');
                            Config::post('categoryAdd', Meta::class, 'addCategory');
                        });
                    });
                });
            }
        );
    }

    public static function essay(): void
    {
        Config::middleware([Limiter::class],
            function () {
                Config::group(['essay'], function () {


                    Config::middleware([Logging::class], function () {

                        Config::post('add', Essay::class, 'insert');
                        Config::post('list', Essay::class, 'multi');
                        Config::post('page', Essay::class, 'page');

                        Config::group(['category'], function () {
                            Config::post('add', EssayCategory::class, 'insert');
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
                        Config::post('login', Wxmp::class, 'in');
                    });

                    Config::middleware([Logging::class],
                        function () {

                        }
                    );
                });
            }
        );
    }

    public static function admin(): void
    {
        Config::group(['admin'], function () {
            Config::middleware([Limiter::class], function () {
                Config::post('login', Login::class, 'in');
            });
            Config::middleware([Logging::class], function () {
                Config::group(['user'], function () {
                    Config::post('info', AdminFetch::class, 'info');
                });
            });
        });
    }

    public static function usual(): void
    {

    }

}