<?php

namespace Yonna\Log;

class Config
{

    /**
     * 日志文件目录根
     * @var string
     */
    private static string $file_path_root = '';

    /**
     * 日志文件目录名
     * @var string
     */
    private static string $file_dir_name = 'y_log';

    /**
     * 文件日志的过期天数
     * @var int
     */
    private static int $file_expire_day = 0;

    /**
     * @return string
     */
    public static function getFilePathRoot(): string
    {
        return self::$file_path_root;
    }

    /**
     * @param string $file_path_root
     */
    public static function setFilePathRoot(string $file_path_root): void
    {
        self::$file_path_root = $file_path_root;
    }

    /**
     * @return string
     */
    public static function getFileDirName(): string
    {
        return self::$file_dir_name;
    }

    /**
     * @param string $file_dir_name
     */
    public static function setFileDirName(string $file_dir_name): void
    {
        self::$file_dir_name = $file_dir_name;
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