<?php

namespace Yonna\QuickStart\Scope\Xoss;

use Yonna\Database\DB;
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
        print_r($fd);
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
        print_r($saveData);
        if (!is_file($saveData['uri'])) {
            $size = @file_put_contents($saveData['uri'], $data);
            if ($size === false) {
                return $this->false('Save failed');
            }
        }
        exit();


        $dir = $hash[0];
        $hash_file_name = $hashId . '.' . $fd['ext'];
        $download_url = $hash[1] . $hash_file_name;
        $filename = $fd['name'] ?: $hashId . '.' . $fd['ext'];
        $dir = dirCheck($dir, true);
        if (!$dir) return $this->false('dir not exist');
        if (!is_file($dir . $hash_file_name)) {
            $size = @file_put_contents($dir . $hash_file_name, $data);
            if ($size === false) {
                return $this->false('can not save data to the path');
            }
        }
        $fileData = DB::connect()->table('assets')->equalTo('hash_id', $hashId)->one();
        if (!$fileData) {
            $insertData = array(
                'create_time' => $this->db()->now(),
                'uid' => array($this->getBean()->getAuthUid()),
                'hash_id' => $hashId,
                'hash_file_name' => $hash_file_name,
                'file_name' => $filename,
                'file_ext' => $fd['ext'],
                'file_size' => $size,
                'content_type' => $fd['type'],
                'from_url' => $fd['tmp_name'],
                'download_url' => $download_url,
                'path' => $dir . $hash_file_name,
            );
            try {
                $this->db()->table('assets')->insert($insertData);
            } catch (\Exception $e) {
                return $this->false($e->getMessage());
            }
            $fileData = $this->db()->table('assets')->equalTo('hash_id', $hashId)->one();
            if (!$fileData) {
                return $this->false('file data error');
            }
        }
        if (!in_array($this->getBean()->getAuthUid(), $fileData['assets_uid'])) {
            $fileData['assets_uid'][] = $this->getBean()->getAuthUid();
            $updateData = array(
                'uid' => $fileData['assets_uid'],
                'update_time' => $this->db()->now(),
            );
            try {
                $this->db()->table('assets')->equalTo('hash_id', $hashId)->update($updateData);
            } catch (\Exception $e) {
            }
        }
        $fileData['assets_error'] = 0;
        $fileData['assets_file_image'] = $this->getHost() . $this->assetsHelper()->getImageByContentType($fileData['assets_file_ext'], $fileData['assets_download_url']);
        $fileData['assets_download_url'] = $this->getHost() . $download_url;
        unset($fileData['assets_uid']);
        unset($fileData['assets_hash_id']);
        unset($fileData['assets_path']);
        unset($fileData['assets_from_url']);
        unset($fileData['assets_call_qty']);
        unset($fileData['assets_call_last_time']);
        unset($fileData['assets_create_time']);
        unset($fileData['assets_update_time']);
        return $fileData;
    }

    private function analysisFile()
    {
        $files = $this->request()->getFiles();
        print_r($files);
        $results = [];
        foreach ($files as $fd) {
            $fd = $this->checkFileData($fd);
            if (!$fd) {
                $results[] = [
                    "result" => 0,
                    "msg" => $this->falseMsg,
                ];
            } else {
                $this->saveFile($fd);
            }
        }

    }

    public function upload()
    {
        $this->analysisFile();
        return true;
    }

}