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
use Yonna\QuickStart\Scope\Feedback;
use Yonna\QuickStart\Scope\League;
use Yonna\QuickStart\Scope\LeagueMember;
use Yonna\QuickStart\Scope\LeagueTask;
use Yonna\QuickStart\Scope\LeagueTaskAssign;
use Yonna\QuickStart\Scope\LeagueTaskJoiner;
use Yonna\QuickStart\Scope\License;
use Yonna\QuickStart\Scope\DataSpeciality;
use Yonna\QuickStart\Scope\DataWork;
use Yonna\QuickStart\Scope\Essay;
use Yonna\QuickStart\Scope\EssayCategory;
use Yonna\QuickStart\Scope\Sdk;
use Yonna\QuickStart\Scope\SdkWxmp;
use Yonna\QuickStart\Scope\User;
use Yonna\QuickStart\Scope\UserAccount;
use Yonna\QuickStart\Scope\Me;
use Yonna\QuickStart\Scope\UserLogin;
use Yonna\QuickStart\Scope\UserMetaCategory;
use Yonna\QuickStart\Scope\Stat;
use Yonna\QuickStart\Scope\UserWechat;
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
        Config::middleware([Limiter::class],
            function () {
                Config::group(['stat'], function () {
                    Config::post('user', Stat::class, 'user');
                    Config::post('userAccount', Stat::class, 'account');
                    Config::post('league', Stat::class, 'league');
                    Config::post('leagueMember', Stat::class, 'leagueMember');
                    Config::post('task', Stat::class, 'task');
                    Config::post('leagueData', Stat::class, 'leagueData');
                    Config::post('essay', Stat::class, 'essay');
                    Config::post('essayCategory', Stat::class, 'essayCategory');
                    Config::post('userGrow', Stat::class, 'userGrow');
                    Config::post('leagueGrow', Stat::class, 'leagueGrow');
                    Config::post('taskGrow', Stat::class, 'taskGrow');
                    Config::post('essayGrow', Stat::class, 'essayGrow');
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

                    Config::post('list', Sdk::class, 'multi');
                    Config::post('info', Sdk::class, 'one');
                    Config::post('edit', Sdk::class, 'update');

                    Config::group(['wxmp'], function () {
                        Config::post('oauth', SdkWxmp::class, 'oauth');
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
                    Config::post('scopes', License::class, 'scopes');
                    Config::post('info', License::class, 'one');
                    Config::post('add', License::class, 'insert');
                    Config::post('edit', License::class, 'update');
                    Config::post('del', License::class, 'delete');
                });
            }
        );
    }

    public static function me(): void
    {
        Config::middleware([Limiter::class, Logging::class], function () {
            Config::group(['me'], function () {
                Config::post('info', Me::class, 'one');
                Config::post('password', Me::class, 'password');
                Config::post('edit', Me::class, 'update');
                Config::post('leagueCanJoin', Me::class, 'leagueCanJoin');
                Config::post('leagueApply', Me::class, 'leagueApply');
                Config::post('leagueGiveUpApply', Me::class, 'leagueGiveUpApply');
                Config::post('leagueLeave', Me::class, 'leagueLeave');
                Config::post('taskInfo', Me::class, 'taskInfo');
                Config::post('taskCanAssign', Me::class, 'taskCanAssign');
                Config::post('taskCanJoin', Me::class, 'taskCanJoin');
                Config::post('taskAssign', Me::class, 'taskAssign');
                Config::post('taskUnAssign', Me::class, 'taskUnAssign');
                Config::post('taskJoin', Me::class, 'taskJoin');
                Config::post('taskUnJoin', Me::class, 'taskUnJoin');
                Config::post('taskPublishList', Me::class, 'taskPublishList');
                Config::post('taskJoinLIst', Me::class, 'taskJoinLIst');
            });
        });
    }

    public static function user(): void
    {
        Config::middleware([Limiter::class], function () {

            Config::group(['user'], function () {
                Config::post('login', UserLogin::class, 'in');
                Config::post('logging', UserLogin::class, 'isLogging');
                Config::post('logout', UserLogin::class, 'out');
                Config::middleware([Logging::class], function () {
                    Config::post('info', User::class, 'one');
                    Config::post('list', User::class, 'multi');
                    Config::post('page', User::class, 'page');
                    Config::post('add', User::class, 'insert');
                    Config::post('edit', User::class, 'update');
                    Config::post('mStatus', User::class, 'multiStatus');
                });
            });

            Config::group(['user', 'account'], function () {
                Config::middleware([Logging::class], function () {
                    Config::post('info', UserAccount::class, 'one');
                    Config::post('edit', UserAccount::class, 'update');
                });
            });

            Config::group(['user', 'wechat'], function () {
                Config::middleware([Logging::class], function () {
                    Config::post('bind', UserWechat::class, 'bind');
                });
            });
        });
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
        Config::middleware([Limiter::class], function () {

            Config::group(['data', 'hobby'], function () {
                Config::post('list', DataHobby::class, 'multi');
            });
            Config::group(['data', 'speciality'], function () {
                Config::post('list', DataSpeciality::class, 'multi');
            });
            Config::group(['data', 'work'], function () {
                Config::post('list', DataWork::class, 'multi');
            });

            Config::middleware([Logging::class], function () {
                Config::group(['data', 'hobby'], function () {
                    Config::post('info', DataHobby::class, 'one');
                    Config::post('page', DataHobby::class, 'page');
                    Config::post('add', DataHobby::class, 'insert');
                    Config::post('edit', DataHobby::class, 'update');
                    Config::post('del', DataHobby::class, 'delete');
                    Config::post('mDel', DataHobby::class, 'multiDelete');
                    Config::post('mStatus', DataHobby::class, 'multiStatus');
                });
                Config::group(['data', 'speciality'], function () {
                    Config::post('info', DataSpeciality::class, 'one');
                    Config::post('page', DataSpeciality::class, 'page');
                    Config::post('add', DataSpeciality::class, 'insert');
                    Config::post('edit', DataSpeciality::class, 'update');
                    Config::post('del', DataSpeciality::class, 'delete');
                    Config::post('mDel', DataSpeciality::class, 'multiDelete');
                    Config::post('mStatus', DataSpeciality::class, 'multiStatus');
                });
                Config::group(['data', 'work'], function () {
                    Config::post('info', DataWork::class, 'one');
                    Config::post('page', DataWork::class, 'page');
                    Config::post('add', DataWork::class, 'insert');
                    Config::post('edit', DataWork::class, 'update');
                    Config::post('del', DataWork::class, 'delete');
                    Config::post('mDel', DataWork::class, 'multiDelete');
                    Config::post('mStatus', DataWork::class, 'multiStatus');
                });
            });
        });
    }

    public static function league(): void
    {
        Config::middleware([Limiter::class, Logging::class], function () {
            Config::group(['league'], function () {
                Config::post('info', League::class, 'one');
                Config::post('list', League::class, 'multi');
                Config::post('page', League::class, 'page');
                Config::post('add', League::class, 'insert');
                Config::post('edit', League::class, 'update');
                Config::post('editc', League::class, 'updatec');
                Config::post('del', League::class, 'delete');
                Config::post('mStatus', League::class, 'multiStatus');
            });
            Config::group(['league', 'task'], function () {
                Config::post('info', LeagueTask::class, 'one');
                Config::post('list', LeagueTask::class, 'multi');
                Config::post('page', LeagueTask::class, 'page');
                Config::post('add', LeagueTask::class, 'insert');
                Config::post('edit', LeagueTask::class, 'update');
                Config::post('del', LeagueTask::class, 'delete');
                Config::post('mStatus', LeagueTask::class, 'multiStatus');
            });
            Config::group(['league', 'task', 'assign'], function () {
                Config::post('list', LeagueTaskAssign::class, 'multi');
                Config::post('add', LeagueTaskAssign::class, 'insert');
                Config::post('del', LeagueTaskAssign::class, 'delete');
            });
            Config::group(['league', 'task', 'joiner'], function () {
                Config::post('page', LeagueTaskJoiner::class, 'page');
                Config::post('add', LeagueTaskJoiner::class, 'insert');
                Config::post('del', LeagueTaskJoiner::class, 'delete');
            });
        });
    }

    public static function leagueMember(): void
    {
        Config::middleware([Limiter::class, Logging::class], function () {
            Config::group(['league', 'member'], function () {
                Config::post('info', LeagueMember::class, 'one');
                Config::post('list', LeagueMember::class, 'multi');
                Config::post('page', LeagueMember::class, 'page');
                Config::post('add', LeagueMember::class, 'insert');
                Config::post('edit', LeagueMember::class, 'update');
                Config::post('del', LeagueMember::class, 'delete');
                Config::post('status', LeagueMember::class, 'status');
                Config::post('mStatus', LeagueMember::class, 'multiStatus');
            });
        });
    }

    public static function feedback(): void
    {
        Config::middleware([Limiter::class, Logging::class], function () {
            Config::group(['feedback'], function () {
                Config::post('list', Feedback::class, 'multi');
                Config::post('page', Feedback::class, 'page');
                Config::post('add', Feedback::class, 'insert');
                Config::post('edit', Feedback::class, 'update');
                Config::post('del', Feedback::class, 'delete');
                Config::post('mDel', Feedback::class, 'multiDelete');
                Config::post('mAnswer', Feedback::class, 'multiAnswer');
            });
        });
    }

}