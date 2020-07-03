<?php

namespace Yonna\Bootstrap;

use Yonna\Foundation\System;
use Yonna\IO\Request;
use Yonna\Mapping\Mapping;
use Yonna\Scope\Config as SC;
use Yonna\QuickStart\Middleware\Limiter;

class Config
{
    /**
     * @param Cargo $Cargo
     * @return Cargo
     */
    public static function install(Cargo $Cargo): Cargo
    {
        // self config files
        $config_root = System::dirCheck($Cargo->getRoot() . '/App/Config', true);
        System::dirRequire($config_root);
        // default scopes
        SC::middleware([Limiter::class],
            function () {
                SC::post('MAPPING', function (Request $request) {
                    $keys = $request->getInput('keys');
                    if (!$keys) {
                        return [];
                    }
                    $target = $request->getInput('target');
                    $method = $request->getInput('method');
                    $method = $method ? 'to' . ucfirst($method) : 'toKv';
                    $maps = [];
                    foreach ($keys as $k) {
                        $kArr = explode('_', $k);
                        $kMap = "\\" . implode("\\", $kArr);
                        $objMap = new $kMap();
                        if ($objMap instanceof Mapping && method_exists($objMap, $method)) {
                            $maps[$k] = $objMap->$method($target ?? 'label');
                        }
                    }
                    return $maps;
                });
            }
        );
        return $Cargo;
    }
}