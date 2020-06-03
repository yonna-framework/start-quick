<?php

namespace Yonna\Foundation;

class System
{

    /**
     * 记录和统计时间（微秒）和内存使用情况
     * 使用方法:
     * rem('begin'); // 记录开始标记位
     * // ... 区间运行代码
     * rem('end'); // 记录结束标签位
     * echo rem('begin','end',6); // 统计区间运行时间 精确到小数后6位
     * echo rem('begin','end','m'); // 统计区间内存使用情况
     * 如果end标记位没有定义，则会自动以当前作为标记位
     * @param string $start 开始标签
     * @param string $end 结束标签
     * @param integer|string $dec 小数位或者m
     * @return mixed
     */
    public static function rem($start, $end = '', $dec = 4)
    {
        static $info = [];
        static $mem = [];
        $memoryLimitOn = function_exists('memory_get_usage');
        if (is_float($end)) { // 记录时间
            $info[$start] = $end;
        } elseif (!empty($end)) { // 统计时间和内存使用
            if (!isset($info[$end])) $info[$end] = microtime(TRUE);
            if ($memoryLimitOn && $dec == 'm') {
                if (!isset($mem[$end])) $mem[$end] = memory_get_usage();
                return number_format(($mem[$end] - $mem[$start]) / 1024);
            } else {
                return number_format(($info[$end] - $info[$start]), $dec);
            }

        } else { // 记录时间和内存使用
            $info[$start] = microtime(TRUE);
            if ($memoryLimitOn) $mem[$start] = memory_get_usage();
        }
        return null;
    }

    /**
     * @param $str
     * @param int $complexity
     * @return false|string
     */
    public static function execEncode($str, $complexity = 2)
    {
        $content = base64_encode($str);
        $content = bin2hex($content);
        $content = str_split($content, $complexity);
        foreach ($content as &$v) {
            $v = $v . rand(10, 99);
        }
        $content = implode('', $content);
        return hex2bin($content);
    }

    /**
     * 区分大小写的文件存在判断
     * @param string $filename 文件地址
     * @return boolean
     */
    public static function fileExistsCase($filename)
    {
        if (is_file($filename)) {
            if (Is::windows()) {
                if (basename(realpath($filename)) != basename($filename)) {
                    return false;
                }
            }
            return true;
        }
        return false;
    }

    /**
     * 获取文件后缀名
     * @param string $filename 文件地址
     * @return boolean
     */
    public static function fileExt($filename)
    {
        $f = explode('.', $filename);
        return array_pop($f);
    }

    /**
     * 优化的require_once
     * @param string $filename 文件地址
     * @return boolean
     */
    public static function fileRequire($filename)
    {
        static $_importFiles = [];
        if (!isset($_importFiles[$filename])) {
            if (static::fileExistsCase($filename)) {
                require(self::fileExt($filename) == 'jar' ? 'java://' . file_get_contents($filename) : $filename);
                $_importFiles[$filename] = true;
            } else {
                $_importFiles[$filename] = false;
            }
        }
        return $_importFiles[$filename];
    }

    /**
     * 载入目录
     * @param $dir
     * @return int|void
     */
    public static function dirRequire($dir)
    {
        if (!is_dir($dir)) return;
        $files = opendir($dir);
        while ($file = readdir($files)) {
            if ($file != '.' && $file != '..') {
                $realFile = $dir . '/' . $file;
                if (is_dir($realFile)) {
                    static::dirRequire($realFile);
                } else {
                    static::fileRequire($realFile);
                }
            }
        }
        closedir($files);
    }

    /**
     * 检查路径目录是否存在，存在则返回真实路径，不存在返回false
     * @param $path
     * @param bool $isBuild 是否自动创建不存在的目录
     * @return bool|string
     */
    public static function dirCheck($path, $isBuild = false)
    {
        $temp = str_replace('\\', '/', $path);
        if ($isBuild) {
            $p = explode('/', $temp);
            $tempLen = count($p);
            $temp = '';
            for ($i = 0; $i < $tempLen; $i++) {
                $temp .= $p[$i] . DIRECTORY_SEPARATOR;
                if (!is_dir($temp)) {
                    mkdir($temp);
                    @chmod($temp, 0777);
                }
            }
        }
        $temp = realpath($temp) . DIRECTORY_SEPARATOR;
        return $temp ? $temp : false;
    }

    /**
     * 递归删除目录
     * @param $dir
     */
    public static function dirDel($dir)
    {
        if (!is_dir($dir)) {
            return;
        }
        $files = opendir($dir);
        while (false !== ($file = readdir($files))) {
            if ($file != '.' && $file != '..') {
                $realDir = realpath($dir);
                $realFile = $realDir . DIRECTORY_SEPARATOR . $file;
                if (is_dir($realFile)) {
                    static::dirDel($realFile);
                    @rmdir($realFile);
                } else {
                    @unlink($realFile);
                }
            }
        }
        closedir($files);
        @rmdir($dir);
    }


    /**
     * 获得所有的 Cipher Methods
     * @return array
     */
    public static function getOpensslCipherMethods()
    {
        return openssl_get_cipher_methods();
    }

    /**
     * 环境配置
     * @param $key
     * @param null $default
     * @return array|bool|false|string|null
     */
    public static function env($key, $default = null)
    {
        $value = getenv($key);
        if ($value === false) {
            return $default;
        }
        switch (strtolower($value)) {
            case 'true':
            case '(true)':
                return true;
            case 'false':
            case '(false)':
                return false;
            case 'empty':
            case '(empty)':
                return '';
            case 'null':
            case '(null)':
                return null;
        }
        if (strlen($value) > 1 && Str::startsWith($value, '"') && Str::endsWith($value, '"')) {
            return substr($value, 1, -1);
        }
        return $value;
    }

}