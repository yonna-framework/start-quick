<?php

class PackageStream
{
    private $string;
    private $position;

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

    public function stream_eof()
    {
    }

    public function stream_stat()
    {
    }

}

stream_wrapper_register('java', PackageStream::class);