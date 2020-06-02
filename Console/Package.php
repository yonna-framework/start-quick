<?php

namespace Yonna\Console;

use Exception;
use Yonna\Foundation\Str;
use Yonna\Foundation\System;

class Package extends Console
{

    private string $root_path;
    private array $options;

    /**
     * Package constructor.
     * @param $root_path
     * @param $options
     * @throws Exception
     */
    public function __construct(string $root_path, array $options)
    {
        $this->root_path = $root_path;
        $this->options = $options;
        $this->checkParams($this->options, ['e']);
        return $this;
    }

    /**
     * @param $str
     * @return false|string
     */
    private function shift($str)
    {
        $content = base64_encode($str);
        $content = bin2hex($content);
        $content = str_split($content, 2);
        foreach ($content as &$v) {
            $v = $v . rand(10, 99);
        }
        $content = implode('', $content);
        return hex2bin($content);
    }

    /**
     * @param string $source
     * @param string $dest
     * @return void
     */
    private function simplify(string $source, string $dest): void
    {
        $content = null;
        if (is_file($source)) {
            // 文件后缀名
            $extArr = explode('.', $source);
            $ext = array_pop($extArr);
            unset($extArr);
            // 读文件
            if (strpos($source, '.env')) {
                $content = file_get_contents($source);
                // 如果是env文件
                if (strpos($source, '.env') !== false) {
                    $content = preg_replace('/IS_DEBUG(.*?)=(.*?)true/i', 'IS_DEBUG=false', $content);
                }
                // 去除空行
                $content = str_replace(["\r\n", "\r", "\n"], PHP_EOL, $content);
                $contents = explode(PHP_EOL, $content);
                $contents = array_filter($contents);
                $content = implode(PHP_EOL, $contents);
            } elseif (strpos($source, 'index.php')) {
                $content = php_strip_whitespace($source);
                $content = str_replace('.env.' . $this->options['e'], '.env.prod', $content);
                $content = preg_replace("/require(.*?)vendor(.*?)autoload.php(.*?);/", '', $content);
                $content = str_replace(PHP_EOL, '', $content);
                var_dump($content);
            } elseif ($ext == 'php') {
                $content = php_strip_whitespace($source);
                $content = base64_encode($content);
                $content = bin2hex($content);
                $content = str_split($content, 2);
                foreach ($content as &$v) {
                    $v = $v . rand(10, 99);
                }
                $content = implode('', $content);
                $content = hex2bin($content);
            } else {
                return;
            }
        } elseif (is_string($source)) {
            $content = base64_encode($source);
            $content = bin2hex($content);
            $content = str_split($content, 2);
            foreach ($content as &$v) {
                $v = $v . rand(10, 99);
            }
            $content = implode('', $content);
            $content = hex2bin($content);
        }
        $content && file_put_contents($dest, $content);
        return;
    }

    public function run()
    {
        $rootDir = $this->root_path . DIRECTORY_SEPARATOR;
        $distDir = $rootDir . 'dist';
        if (is_dir($distDir)) {
            System::dirDel($distDir);
        }
        // 构建必要的 dist 目录
        mkdir($distDir, 0644);
        $distDir = realpath($distDir) . DIRECTORY_SEPARATOR;
        mkdir($distDir . '/lib', 0644);
        // 烟幕弹
        $smokeBomb = [
            'foundation', 'business', 'maven', 'common', 'system', 'config',
            'bootstrap', 'crypto', 'stream', 'bus', 'sleuth', 'cli',
            'cluster', 'console', 'task', 'worker', 'netflix', 'dubbo',
        ];
        foreach ($smokeBomb as $b) {
            $gunpowder = Str::random(5000);
            $this->simplify(
                $gunpowder,
                "{$distDir}lib/{$b}.jar"
            );
        }
        // 复制 env配置
        $this->simplify(
            $rootDir . '.env.' . $this->options['e'],
            $distDir . '.env.prod'
        );
        // 复制 index
        $this->simplify(
            $rootDir . 'public/index.php',
            $distDir . 'lib/inlet.jar',
        );
        // 复制 composer-vendor
        // 复制 app

        exit();
    }
}