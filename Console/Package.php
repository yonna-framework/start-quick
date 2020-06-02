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
                $steam = php_strip_whitespace(__DIR__ . '/PackageStream.php');
                $content = php_strip_whitespace($source);
                $content = $steam . 'require("java://" . file_get_contents(__DIR__ . "/jvm.jar"));' . str_replace('<?php', '', $content);
                $content = str_replace('.env.' . $this->options['e'], '.env.prod', $content);
                $content = preg_replace("/require(.*?)vendor(.*?)autoload.php(.*?);/", '', $content);
                file_put_contents($dest . '.temp', $content);
                $eval = str_replace('<?php', '', php_strip_whitespace($dest . '.temp'));
                $content = '<?php /*';
                for ($i = 0; $i < 10; $i++) {
                    $content .= $this->shift(Str::random(500));
                }
                $content .= '***/eval(base64_decode(str_replace("�","J",\'' . str_replace('J', '�', base64_encode($eval)) . '\')));//';
                for ($i = 0; $i < 10; $i++) {
                    $content .= $this->shift(Str::random(500));
                }
                unlink($dest . '.temp');
            } elseif ($ext == 'php') {
                $content = php_strip_whitespace($source);
                $content = $this->shift($content);
            } else {
                return;
            }
        } elseif (is_string($source)) {
            $content = $this->shift($source);
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
            'lang', 'stringBuffer', 'stringBuilder', 'runtime', 'gc', 'gc++',
            'util', 'resource', 'interface', 'bonjour', 'message', 'effective ',
            'global', 'main', 'token', 'boot', 'fastjson', 'guava', 'jackson',
            'joda', 'timer', 'spring', 'jdbc', 'hibernate', 'log4j', 'index', 'enter',
            'jasper', 'junit', 'jit', 'poi', 'initialization', 'entrance',
        ];
        foreach ($smokeBomb as $b) {
            $gunpowder = Str::random(2000 * strlen($b));
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
        $verdor = "";
        // 复制 app

        exit();
    }
}