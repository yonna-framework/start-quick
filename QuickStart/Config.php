<?php

namespace Yonna\QuickStart;

class Config
{

    private static string $app_root = "";

    /**
     * @return string
     */
    public static function getAppRoot(): string
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

}