<?php

namespace Yonna\QuickStart\Scope;

use Yonna\IO\Request;
use Yonna\QuickStart\Middleware\Debug;
use Yonna\Scope\Config;
use Yonna\QuickStart\Config as QuickStartConfig;
use Yonna\I18n\I18n as I18nModule;
use Yonna\Throwable\Exception\DatabaseException;


class I18n
{

    /**
     * I18n constructor.
     * @throws DatabaseException
     */
    public function __construct()
    {
        $file = QuickStartConfig::getAppRoot() . '/i18n';
        if (!is_file($file)) {
            file_put_contents($file, '');
            (new I18nModule())->initDatabase();
        }
    }

    public static function install()
    {
        Config::middleware( // only debug
            [
                Debug::class,
            ],
            function () {
                Config::group(['i18n'], function () {
                    Config::delete('set', function (Request $request) {
                        $input = $request->getInput();
                        $data = [];
                        foreach (I18nModule::ALLOW_LANG as $lang) {
                            $data[$lang] = $input[$lang] ?? '';
                        }
                        (new I18nModule())->set($input['unique_key'], $data);
                        return true;
                    });
                });
            }
        );
        Config::group(['i18n'], function () {
            Config::put('backup', function () {
                return (new I18nModule())->backup();
            });
            Config::post('all', function () {
                return (new I18nModule())->get();
            });
            Config::post('page', function (Request $request) {
                $input = $request->getInput();
                return (new I18nModule())->page(
                    $input['current'] ?? 1,
                    $input['per'] ?? 10,
                    [
                        'unique_key' => $input['unique_key'] ?? null,
                    ],
                );
            });
        });
    }

}