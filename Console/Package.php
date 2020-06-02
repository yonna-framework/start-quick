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

    private function codesPHP($dir, $removeDir, $exclude = [])
    {
        if (is_dir($dir)) {
            $dir = realpath($dir);
            $files = opendir($dir);
            while ($file = readdir($files)) {
                if ($file != '.' && $file != '..' && !in_array($file, $exclude)) {
                    $fileOpt = explode('.', $file);
                    $fileExt = array_pop($fileOpt);
                    $fileName = implode('.', $fileOpt);
                    $filePath = $dir . '/' . $file;
                    if (is_dir($filePath)) {
                        $this->codesPHP($filePath, $removeDir, $exclude);
                    } elseif ($fileExt == 'php') {
                        $newDir = $this->root_path . '/dist/library/' . str_replace($removeDir, '', $dir) . '/';
                        System::dirCheck($newDir, true);
                        $newDir = realpath($newDir);
                        echo("PKG => {$newDir}/{$fileName}.jar\n");
                        file_put_contents("{$newDir}/{$fileName}.jar", php_strip_whitespace($filePath));
                    } elseif ($fileExt == 'json') {
                        //$codes .= str_replace('<?php', '', php_strip_whitespace($filePath)) . PHP_EOL;
                    }
                }
            }
            closedir($files);
        }
    }

    /**
     * @param string $source
     * @param string $dest
     * @return void
     */
    private
    function simplify(string $source, string $dest): void
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

    public
    function run()
    {
        $rootDir = $this->root_path . DIRECTORY_SEPARATOR;
        $distDir = $rootDir . 'dist';
        if (is_dir($distDir)) {
            System::dirDel($distDir);
        }
        // 构建必要的 dist 目录
        mkdir($distDir, 0644);
        $distDir = realpath($distDir) . DIRECTORY_SEPARATOR;
        mkdir($distDir . '/boot', 0644);
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
                "{$distDir}boot/{$b}.jar"
            );
        }
        // 打包 env配置
        $this->simplify(
            $rootDir . '.env.' . $this->options['e'],
            $distDir . '.env.prod'
        );
        // 打包 index
        $this->simplify(
            $rootDir . 'public/index.php',
            $distDir . 'boot/inlet.jar',
        );
        // 打包 composer-vendor-yonna
        $vendorRoot = (realpath($this->root_path));
        $codes = $this->codesPHP(
            realpath($this->root_path . '/vendor/yonna/yonna'),
            realpath($this->root_path . '/vendor/yonna/yonna'),
            [
                'Package.php',
                'PackageStream.php',
                'Swoole',
            ]
        );
//        $codes = str_replace(PHP_EOL, '', $codes);
        file_put_contents($distDir . "boot/jvm.php", $codes);
        // 复制 app

        exit();
    }
}