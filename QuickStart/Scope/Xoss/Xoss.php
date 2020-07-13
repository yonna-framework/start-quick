<?php

namespace Yonna\QuickStart\Scope\Xoss;

use Yonna\Database\DB;
use Yonna\Database\Driver\Pdo\Where;
use Yonna\Foundation\System;
use Yonna\QuickStart\Helper\Assets;
use Yonna\QuickStart\Scope\AbstractScope;

/**
 * Class Xoss
 * @package Yonna\QuickStart\Scope\User
 */
class Xoss extends AbstractScope
{

    /**
     * @var string
     */
    private string $falseMsg = "";

    /**
     * @param $msg
     * @return bool
     */
    private function false($msg)
    {
        $this->falseMsg = $msg;
        return false;
    }

    /**
     * @param $data
     * @return array|bool
     */
    private function checkFileData($data)
    {
        if (!$data) {
            return $this->false("no file data");
        }
        // 检查是否上传失败
        if (!empty($data['error'])) {
            switch ($data['error']) {
                case '1':
                    $error = 'out of size in .ini';
                    break;
                case '2':
                    $error = 'out of form size';
                    break;
                case '3':
                    $error = 'file is damaged';
                    break;
                case '4':
                    $error = 'file lose';
                    break;
                case '6':
                    $error = 'temp file not found';
                    break;
                case '7':
                    $error = 'hard disk error';
                    break;
                case '8':
                    $error = 'aborted by extended file upload';
                    break;
                case '999':
                default:
                    $error = 'unknow';
            }
            return $this->false($error);
        }
        // 检查文件名
        if (!$data['tmp_name']) {
            return $this->false("file name error");
        }
        // 检查临时文件是否已上传
        if (@is_uploaded_file($data['tmp_name']) === false) {
            return $this->false("upload fail");
        }
        // 获得文件扩展名
        $suffix = Assets::getSuffix($data['name'], $data['type']);
        if (!$suffix) {
            return $this->false('invalid suffix');
        }
        if (!Assets::checkExt($suffix)) {
            return $this->false('not allow suffix');
        }
        $data['suffix'] = $suffix;
        return $data;
    }

    /**
     * @param $fd
     * @return bool
     */
    private function saveFile($fd)
    {
        $size = $fd['size'];
        if ($size > 0) {
            $data = file_get_contents($fd['tmp_name']);
            if (!$data) {
                return $this->false('invalid tmp data');
            }
        } else {
            $data = '';
        }
        $saveData = [
            'name' => $fd['name'],
            'suffix' => $fd['suffix'],
            'size' => $fd['size'],
            'content_type' => $fd['type'],
        ];
        $md5 = md5($data);
        $sha1 = sha1($data);
        $hash = $sha1 . $md5;
        $saveData['hash'] = $hash;
        $saveData['key'] = $hash; // 这个key用于访问资源，默认是hash
        $saveData['md5_name'] = $md5;
        $saveData['path'] = $this->request()->getCargo()->getRoot() . '/Uploads/' . date('Y-m-d') . "/" . date('H') . "/";
        $saveData['uri'] = $saveData['path'] . $md5 . '.' . $fd['suffix'];
        if (!System::dirCheck($saveData['path'], true)) {
            return $this->false('invalid dir');
        }
        if (!is_file($saveData['uri'])) {
            $size = @file_put_contents($saveData['uri'], $data);
            if ($size === false) {
                return $this->false('Save failed');
            }
        }
        if ($this->request()->getLoggingId()) {
            $saveData['user_id'] = $this->request()->getLoggingId();
        }
        $fileData = DB::connect()->table('xoss')
            ->field('key,name,suffix,size,content_type')
            ->where(fn(Where $w) => $w->equalTo('hash', $hash))
            ->one();
        if (!$fileData) {
            DB::connect()->table('xoss')->insert($saveData);
            $fileData = DB::connect()->table('xoss')
                ->field('key,name,suffix,size,content_type')
                ->where(fn(Where $w) => $w->equalTo('hash', $hash))
                ->one();
            if (!$fileData) {
                return $this->false('file data error');
            }
        }
        return $fileData;
    }

    private function analysisFile()
    {
        $files = $this->request()->getFiles();
        $results = [];
        foreach ($files as $fd) {
            $fd = $this->checkFileData($fd);
            if (!$fd) {
                $results[] = [
                    "result" => 0,
                    "msg" => $this->falseMsg,
                    "data" => null,
                ];
            } else {
                $results[] = [
                    "result" => 1,
                    "msg" => 'success',
                    "data" => $this->saveFile($fd)
                ];
            }
        }
        return $results;
    }

    public function upload()
    {
        return $this->analysisFile();
    }

}