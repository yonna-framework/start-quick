<?php


class PackageStream
{
    private string $string;
    private int $position;

    public function stream_open($path)
    {
        $content = str_replace('java://', '', $path);
        $back = bin2hex($content);
        $back = str_split($back, 4);
        foreach ($back as &$v) {
            $v = substr($v, 0, 2);
        }
        $back = implode('', $back);
        $back = hex2bin($back);
        $this->string = base64_decode($back);
        $this->position = 0;
        return true;
    }

    public function stream_read($count)
    {
        $ret = substr($this->string, $this->position, $count);
        $this->position += strlen($ret);
        return $ret;
    }

//    public static function stream_set_option(int $option, int $arg1, int $arg2): bool
//    {
//        return true;
//    }

    public function stream_eof()
    {
    }

    public function stream_stat()
    {
    }


}

stream_wrapper_register('java', PackageStream::class);
spl_autoload_register(function ($res) {
    $res = str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $res);
    $nameArr = explode('/', $res);
    $firstName = array_shift($nameArr);
    !$firstName && $firstName = array_shift($nameArr);
    $file = null;
    if ($firstName == 'App') {
        $file = __DIR__ . '/../library/App/' . implode('/', $nameArr) . '.jar';
    } elseif ($firstName == 'Yonna') {
        $file = __DIR__ . '/../library/' . implode('/', $nameArr) . '.jar';
    } elseif (in_array($firstName, ['POption', 'Dotenv', 'Symfony', 'Seclib', 'AmqpLib', 'GuzzleHttp', 'Psr', 'Workerman'])) {
        $file = __DIR__ . '/../library/Plugins/' . $res . '.jar';
    }
    var_dump($file);
    if (is_file($file)) {
        require('java://' . file_get_contents($file));
    } else {
        exit($file);
    }
});