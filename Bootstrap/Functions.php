<?php

namespace Yonna\Bootstrap;

use Yonna\Foundation\System;

class Functions
{

    public static function install(Cargo $Cargo): Cargo
    {
        $path = realpath($Cargo->getRoot() . DIRECTORY_SEPARATOR . 'Functions');
        if ($path) {
            System::requireDir($path);
        }
        return $Cargo;
    }

}