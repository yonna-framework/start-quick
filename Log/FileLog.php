<?php

namespace Yonna\Log;

use Throwable;
use Yonna\Foundation\System;

class FileLog
{

    private string $root = '';

    public function __construct()
    {
        $this->root = Config::getFilePathRoot();
    }

    /**
     * 递归删除过期日志
     * @param $dir
     * @param integer $timestamp 删除这一天之前的
     */
    private function dirExpire($dir, $timestamp)
    {
        if (!is_dir($dir)) {
            return;
        }
        $files = opendir($dir);
        while (false !== ($file = readdir($files))) {
            if ($file != '.' && $file != '..') {
                $t = strtotime($file);
                if ($t > $timestamp) {
                    continue;
                }
                $realDir = realpath($dir);
                $realFile = $realDir . DIRECTORY_SEPARATOR . $file;
                if (is_dir($realFile)) {
                    static::dirExpire($realFile, $timestamp);
                    @rmdir($realFile);
                } else {
                    @unlink($realFile);
                }
            }
        }
        closedir($files);
        if ($dir !== $this->root . DIRECTORY_SEPARATOR . Config::getFileDirName()) {
            @rmdir($dir);
        }
    }

    /**
     * 清除文件日志
     */
    private function clear()
    {
        if (Config::getFileExpireDay() <= 0) {
            return;
        }
        $this->dirExpire($this->root . DIRECTORY_SEPARATOR . Config::getFileDirName(), time() - 86400 * Config::getFileExpireDay());
    }

    /**
     * 获取日志目录，以天分割
     * @param $key
     * @return string
     */
    private function dir($key)
    {
        $path = $this->root
            . DIRECTORY_SEPARATOR . Config::getFileDirName()
            . DIRECTORY_SEPARATOR . date('Y-m-d')
            . DIRECTORY_SEPARATOR . $key;
        System::dirCheck($path, true);
        return realpath($path) . DIRECTORY_SEPARATOR;
    }

    /**
     * 获取日志文件名
     * @param $type
     * @return string
     */
    private function file($type)
    {
        return strtolower($type) . '.log';
    }

    /**
     * @param $dir
     * @return array
     */
    private function explodeDir($dir)
    {
        $file_arr = scandir($dir);
        $new_arr = [];
        foreach ($file_arr as $f) {
            if ($f != ".." && $f != ".") {
                if (is_dir($dir . DIRECTORY_SEPARATOR . $f)) {
                    array_unshift($new_arr, [
                        'path' => $f,
                        'children' => $this->explodeDir($dir . DIRECTORY_SEPARATOR . $f)
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

    /**
     * 文件目录
     * @return array
     */
    public function catalog()
    {
        $dir = realpath(Config::getFilePathRoot() . DIRECTORY_SEPARATOR . Config::getFileDirName());
        if (!$dir) return [];
        return $this->explodeDir($dir);
    }

    /**
     * 文件内容
     * @param $fileName
     * @return string
     */
    public function fileContent($fileName)
    {
        $file = realpath(Config::getFilePathRoot() . DIRECTORY_SEPARATOR . Config::getFileDirName() . DIRECTORY_SEPARATOR . $fileName);
        if (!is_file($file)) {
            return '';
        }
        $content = file_get_contents($file);
        return str_replace(["\r\n", "\r", "\n", "\t"], '<br/>', $content);
    }

    /**
     * 写入日志
     * @param $type
     * @param array $data
     * @param string $key
     */
    private function append($type, $key, array $data = [])
    {
        if (empty($key) or empty($data)) {
            return;
        }
        $append = '[time:' . date("Y-m-d H:i:s D T") . ']' . PHP_EOL;
        if ($data) {
            foreach ($data as $k => $v) {
                if (is_array($v) || is_object($v)) {
                    $v = json_encode($v, JSON_UNESCAPED_UNICODE);
                } elseif (is_string($v)) {
                    if (strpos($v, 'java://') === 0) {
                        $v = 'jar package';
                    }
                } else {
                    $v = (string)$v;
                }
                $data && $append .= " #{$k} " . $v . PHP_EOL;
            }
        }
        @file_put_contents($this->dir($key) . $this->file($type), $append . PHP_EOL, FILE_APPEND);
        $this->clear();
    }

    /**
     * @param string $key
     * @param Throwable $t
     */
    public function throwable(Throwable $t, $key = 'default')
    {
        $this->append(Type::THROWABLE, $key, [
            'code' => $t->getCode(),
            'message' => $t->getMessage(),
            'file' => $t->getFile(),
            'line' => $t->getLine(),
            'trace' => $t->getTrace(),
        ]);
    }

    /**
     * @param array $data
     * @param string $key
     */
    public function info(array $data = [], $key = 'default')
    {
        $this->append(Type::INFO, $key, $data);
    }

    /**
     * @param array $data
     * @param string $key
     */
    public function warning(array $data = [], $key = 'default')
    {
        $this->append(Type::WARNING, $key, $data);
    }

    /**
     * @param array $data
     * @param string $key
     */
    public function error(array $data = [], $key = 'default')
    {
        $this->append(Type::ERROR, $key, $data);
    }

}