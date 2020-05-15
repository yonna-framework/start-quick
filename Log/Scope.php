<?php

namespace Yonna\Log;

use Yonna\IO\Request;
use Yonna\Scope\Config;
use Yonna\Log\Config as LogConf;

class Scope
{

    private static function myScanDir($dir)
    {
        $file_arr = scandir($dir);
        $new_arr = [];
        foreach ($file_arr as $f) {
            if ($f != ".." && $f != ".") {
                if (is_dir($dir . DIRECTORY_SEPARATOR . $f)) {
                    array_unshift($new_arr, [
                        'path' => $f,
                        'children' => self::myScanDir($dir . DIRECTORY_SEPARATOR . $f)
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

    public static function conf()
    {
        Config::group(['log'], function () {
            Config::post('dir', function () {
                $dir = realpath(LogConf::getDir() . LogConf::getFile());
                $dir = self::myScanDir($dir);
                return $dir;
            });
            Config::post('file', function (Request $request) {
                $file = realpath(LogConf::getDir() . LogConf::getFile() . DIRECTORY_SEPARATOR . $request->getInput()['file']);
                if (!is_file($file)) {
                    return '';
                }
                $content = file_get_contents($file);
                $content = str_replace(["\r\n", "\r", "\n", "\t"], '<br/>', $content);
                return $content;
            });
            Config::post('db', function (Request $request) {
                $input = $request->getInput();
                return Log::db()->page($input);
            });
        });
    }

}