<?php

namespace Yonna\QuickStart;

use Yonna\I18n\I18n as I18nServices;
use Yonna\IO\Request;
use Yonna\Log\Log as LogService;
use Yonna\Log\Prism;
use Yonna\QuickStart\Middleware\Debug;
use Yonna\QuickStart\Middleware\Logging;
use Yonna\Scope\Config;

class Install
{

    private static string $app_root = "";

    /**
     * @return string
     */
    private static function getAppRoot(): string
    {
        return self::$app_root . '/quickInstall';
    }

    /**
     * @param string $app_root
     */
    public static function setAppRoot(string $app_root): void
    {
        self::$app_root = $app_root;
        @mkdir(self::$app_root . '/quickInstall', 0777);
    }

    // log装载
    public static function log(): void
    {
        $file = self::getAppRoot() . '/log';
        if (!is_file($file)) {
            file_put_contents($file, '');
            LogService::db()->initDatabase();
        }
        Config::middleware([Logging::class],
            function () {
                Config::group(['log'], function () {
                    Config::post('catalog', function () {
                        return LogService::file()->catalog();
                    });
                    Config::post('file', function (Request $request) {
                        return LogService::file()->fileContent($request->getInput('file'));
                    });
                    Config::post('db', function (Request $request) {
                        return LogService::db()->page(new Prism($request));
                    });
                });
            }
        );
    }

    // i18n装载
    public static function i18n(): void
    {
        $file = self::getAppRoot() . '/i18n';
        if (!is_file($file)) {
            file_put_contents($file, '');
            (new I18nServices())->initDatabase();
        }
        Config::middleware([Debug::class],
            function () {
                Config::group(['i18n'], function () {
                    Config::delete('set', function (Request $request) {
                        $input = $request->getInput();
                        $data = [];
                        foreach (I18nServices::ALLOW_LANG as $lang) {
                            $data[$lang] = $input[$lang] ?? '';
                        }
                        (new I18nServices())->set($input['unique_key'], $data);
                        return true;
                    });
                });
            }
        );
        Config::middleware([Logging::class], function () {
            Config::group(['i18n'], function () {
                Config::put('backup', function () {
                    return (new I18nServices())->backup();
                });
                Config::post('page', function (Request $request) {
                    $input = $request->getInput();
                    return (new I18nServices())->page(
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
                return (new I18nServices())->get();
            });
        });
    }
}