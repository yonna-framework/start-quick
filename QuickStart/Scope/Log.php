<?php

namespace Yonna\QuickStart\Scope;

use Yonna\IO\Request;
use Yonna\QuickStart\Config as QuickStartConfig;
use Yonna\Scope\Config;
use Yonna\Services\Log\Log as LogService;
use Yonna\Services\Log\Prism;

class Log
{

    /**
     * I18n constructor.
     */
    public function __construct()
    {
        $file = QuickStartConfig::getAppRoot() . '/log';
        if (!is_file($file)) {
            file_put_contents($file, '');
            LogService::db()->initDatabase();
        }
    }

    public function install()
    {
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

}