<?php

namespace Yonna\QuickStart\Scope;

use Yonna\IO\Request;
use Yonna\QuickStart\Config as QuickStartConfig;
use Yonna\Scope\Config;
use Yonna\Log\Config as LogConf;
use Yonna\Log\DatabaseLog;
use Yonna\Log\Log as LogLib;

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
            (new DatabaseLog())->initDatabase();
        }
    }

    private function myScanDir($dir)
    {
        $file_arr = scandir($dir);
        $new_arr = [];
        foreach ($file_arr as $f) {
            if ($f != ".." && $f != ".") {
                if (is_dir($dir . DIRECTORY_SEPARATOR . $f)) {
                    array_unshift($new_arr, [
                        'path' => $f,
                        'children' => $this->myScanDir($dir . DIRECTORY_SEPARATOR . $f)
                    ]);
                } else {
                    $new_arr[] = [
                        'path' => $f,
                    ];
                }
            }
        }
        return $new_arr;
    }

    public function install()
    {
        Config::group(['log'], function () {
            Config::post('dir', function () {
                $dir = realpath(LogConf::getFilePathRoot() . DIRECTORY_SEPARATOR . LogConf::getFileDirName());
                $dir = $this->myScanDir($dir);
                return $dir;
            });
            Config::post('file', function (Request $request) {
                $file = realpath(LogConf::getFilePathRoot() . DIRECTORY_SEPARATOR . LogConf::getFileDirName() . DIRECTORY_SEPARATOR . $request->getInput()['file']);
                if (!is_file($file)) {
                    return '';
                }
                $content = file_get_contents($file);
                $content = str_replace(["\r\n", "\r", "\n", "\t"], '<br/>', $content);
                return $content;
            });
            Config::post('db', function (Request $request) {
                $input = $request->getInput();
                return LogLib::db()->page($input);
            });
        });
    }

}