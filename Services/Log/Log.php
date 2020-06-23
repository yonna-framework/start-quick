<?php

namespace Yonna\Services\Log;

class Log
{
    private static ?FileLog $fileLog = null;
    private static ?DatabaseLog $databaseLog = null;

    /**
     * @return FileLog|null
     */
    public static function file()
    {
        if (self::$fileLog === null) {
            self::$fileLog = new FileLog();
        }
        return self::$fileLog;
    }

    /**
     * @return DatabaseLog|null
     */
    public static function db()
    {
        if (self::$databaseLog === null) {
            self::$databaseLog = new DatabaseLog();
        }
        return self::$databaseLog;
    }

}