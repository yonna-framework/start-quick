<?php
/**
 * Bootstrap ENV Checker
 */

namespace Yonna\Bootstrap;

use Dotenv\Dotenv;
use Exception;

class Env
{
    const MINIMUM_PHP_VERSION = '7.4';

    /**
     * @param Cargo $Cargo
     * @return Cargo
     * @throws Exception
     */
    public static function install(Cargo $Cargo): Cargo
    {
        // dotenv
        if ($Cargo->getEnvName()) {
            if (!is_file($Cargo->getRoot() . DIRECTORY_SEPARATOR . '.env.' . $Cargo->getEnvName())) {
                exit('Need file .env.' . $Cargo->getEnvName());
            }
            Dotenv::createImmutable($Cargo->getRoot(), '.env.' . $Cargo->getEnvName())->load();
        }
        // check php version
        if (version_compare(PHP_VERSION, self::MINIMUM_PHP_VERSION, '<')) {
            echo 'Need PHP >= ' . self::MINIMUM_PHP_VERSION;
            exit;
        }
        // timezone
        if (!defined('TIMEZONE')) {
            define("TIMEZONE", getenv('TIMEZONE') ?? 'PRC');
        }
        date_default_timezone_set(TIMEZONE);
        // debug
        if (getenv('IS_DEBUG') === 'true') {
            error_reporting(E_ALL);
            ini_set('display_errors', 'On');
            $Cargo->setDebug(true);
        } else {
            error_reporting(E_ERROR & E_WARNING & E_NOTICE);
            ini_set('display_errors', 'Off');
            $Cargo->setDebug(false);
        }
        // system
        $isWindows = strstr(PHP_OS, 'WIN') && PHP_OS !== 'CYGWIN';
        // cargo
        $Cargo->setCurrentPhpVersion(PHP_VERSION);
        $Cargo->setEnv($_ENV);
        $Cargo->setMinimumPhpVersion(self::MINIMUM_PHP_VERSION);
        $Cargo->setWindows($isWindows);
        $Cargo->setLinux(!$isWindows);
        $Cargo->setProjectName(getenv('PROJECT_NAME') ?? 'Yonna');
        $Cargo->setTimezone(TIMEZONE);
        return $Cargo;
    }

}