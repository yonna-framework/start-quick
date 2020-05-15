<?php

namespace Yonna\Log;

class Config
{

    /**
     * 日志文件目录
     * @var string
     */
    private static string $dir = '';

    /**
     * 日志文件目录名
     * @var string
     */
    private static string $file = 'yonna_log';

    /**
     * 文件日志的过期天数
     * @var int
     */
    private static int $file_expire_day = 0;

    /**
     * @return string
     */
    public static function getDir(): string
    {
        return self::$dir;
    }

    /**
     * @param string $dir
     */
    public static function setDir(string $dir): void
    {
        self::$dir = $dir;
    }

    /**
     * @return string
     */
    public static function getFile(): string
    {
        return self::$file;
    }

    /**
     * @param string $file
     */
    public static function setFile(string $file): void
    {
        self::$file = $file;
    }

    /**
     * @return int
     */
    public static function getFileExpireDay(): int
    {
        return self::$file_expire_day;
    }

    /**
     * @param int $file_expire_day
     */
    public static function setFileExpireDay(int $file_expire_day): void
    {
        self::$file_expire_day = $file_expire_day;
    }

    //===================================================================

    private static $database = null;

    /**
     * @return null
     */
    public static function getDatabase()
    {
        return self::$database;
    }

    /**
     * @param null $database
     */
    public static function setDatabase($database): void
    {
        self::$database = $database;
    }

}