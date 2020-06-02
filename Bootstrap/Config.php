<?php

namespace Yonna\Bootstrap;

use Yonna\Foundation\System;

class Config
{
    /**
     * @param Cargo $Cargo
     * @return Cargo
     */
    public static function install(Cargo $Cargo): Cargo
    {
        $config_root = System::dirCheck($Cargo->getRoot() . '/App/Config', true);
        System::requireDir($config_root);
        return $Cargo;
    }
}