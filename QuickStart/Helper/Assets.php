<?php

namespace Yonna\QuickStart\Helper;


use Yonna\Foundation\Parse;

class Assets extends AbstractHelper
{

    const HASH_SPLIT_LENGTH = 20;

    private static array $mimes = [
        'xl' => 'application/excel',
        'js' => ['application/javascript', 'application/x-javascript', 'text/javascript'],
        'hqx' => 'application/mac-binhex40',
        'cpt' => 'application/mac-compactpro',
        'bin' => 'application/macbinary',
        'doc' => 'application/msword',
        'word' => 'application/msword',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'xltx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.template',
        'potx' => 'application/vnd.openxmlformats-officedocument.presentationml.template',
        'ppsx' => 'application/vnd.openxmlformats-officedocument.presentationml.slideshow',
        'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'sldx' => 'application/vnd.openxmlformats-officedocument.presentationml.slide',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'dotx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.template',
        'xlam' => 'application/vnd.ms-excel.addin.macroEnabled.12',
        'xlsb' => 'application/vnd.ms-excel.sheet.binary.macroEnabled.12',
        'class' => 'application/octet-stream',
        'dll' => 'application/octet-stream',
        'dms' => 'application/octet-stream',
        'exe' => 'application/octet-stream',
        'lha' => 'application/octet-stream',
        'lzh' => 'application/octet-stream',
        'psd' => 'application/octet-stream',
        'sea' => 'application/octet-stream',
        'so' => 'application/octet-stream',
        'oda' => 'application/oda',
        'pdf' => 'application/pdf',
        'ai' => 'application/postscript',
        'eps' => 'application/postscript',
        'ps' => 'application/postscript',
        'smi' => 'application/smil',
        'smil' => 'application/smil',
        'mif' => 'application/vnd.mif',
        'xls' => 'application/vnd.ms-excel',
        'ppt' => 'application/vnd.ms-powerpoint',
        'wbxml' => 'application/vnd.wap.wbxml',
        'wmlc' => 'application/vnd.wap.wmlc',
        'dcr' => 'application/x-director',
        'dir' => 'application/x-director',
        'dxr' => 'application/x-director',
        'dvi' => 'application/x-dvi',
        'gtar' => 'application/x-gtar',
        'php3' => 'application/x-httpd-php',
        'php4' => 'application/x-httpd-php',
        'php' => 'application/x-httpd-php',
        'phtml' => 'application/x-httpd-php',
        'phps' => 'application/x-httpd-php-source',
        'swf' => 'application/x-shockwave-flash',
        'sit' => 'application/x-stuffit',
        'tar' => 'application/x-tar',
        'tgz' => 'application/x-tar',
        'xht' => 'application/xhtml+xml',
        'xhtml' => 'application/xhtml+xml',
        'zip' => 'application/zip',
        'mid' => 'audio/midi',
        'midi' => 'audio/midi',
        'mp2' => 'audio/mpeg',
        'mp3' => 'audio/mpeg',
        'mpga' => 'audio/mpeg',
        'aif' => 'audio/x-aiff',
        'aifc' => 'audio/x-aiff',
        'aiff' => 'audio/x-aiff',
        'ram' => 'audio/x-pn-realaudio',
        'rm' => 'audio/x-pn-realaudio',
        'rpm' => 'audio/x-pn-realaudio-plugin',
        'ra' => 'audio/x-realaudio',
        'wav' => 'audio/x-wav',
        'bmp' => 'image/bmp',
        'gif' => 'image/gif',
        'jpeg' => 'image/jpeg',
        'jpg' => 'image/jpeg',
        'jpe' => 'image/jpeg',
        'png' => 'image/png',
        'tiff' => 'image/tiff',
        'tif' => 'image/tiff',
        'eml' => 'message/rfc822',
        'css' => 'text/css',
        'html' => 'text/html',
        'htm' => 'text/html',
        'shtml' => 'text/html',
        'log' => 'text/plain',
        'text' => 'text/plain',
        'txt' => 'text/plain',
        'rtx' => 'text/richtext',
        'rtf' => 'text/rtf',
        'vcf' => 'text/vcard',
        'vcard' => 'text/vcard',
        'xml' => 'text/xml',
        'xsl' => 'text/xml',
        'mpeg' => 'video/mpeg',
        'mpe' => 'video/mpeg',
        'mpg' => 'video/mpeg',
        'mov' => 'video/quicktime',
        'qt' => 'video/quicktime',
        'rv' => 'video/vnd.rn-realvideo',
        'avi' => 'video/x-msvideo',
        'movie' => 'video/x-sgi-movie'
    ];

    private static array $allow_mimes = [
        'mp3', 'mp4', 'mpeg-1', 'mpeg-2', 'mpeg-3', 'mpeg-4', 'vob', 'avi', 'rm', 'rmvb', 'wmv', 'mov', 'flv',
        'txt', 'pdf', 'wps', 'tif', 'tiff',
        'doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx', 'csv',
        'rar', '7z', 'zip',
        'gif', 'jpeg', 'jpg', 'bmp', 'png', 'psd',
        'pem',
    ];

    /**
     * 根据资源路径获取Header信息
     * @param $url
     * @return array
     */
    public function getHeaderByUrl($url)
    {
        if (!$url) return array();
        $header = get_headers($url);
        $hd = array(
            'HTTP_VERSION' => '',
            'HTTP_STATUS' => '',
            'HTTP_RESPONSE' => '',
            'FILE_NAME' => '',
        );
        foreach ($header as $h) {
            $h = urldecode($h);
            if (strpos($h, ':') === false) {
                if (strpos($h, 'HTTP') !== false) {
                    $h = explode(' ', $h);
                    $hd['HTTP_VERSION'] = array_shift($h);
                    $hd['HTTP_STATUS'] = array_shift($h);
                    $hd['HTTP_RESPONSE'] = implode(' ', $h);
                }
            } else {
                $h = explode(':', $h);
                $hd[strtoupper(trim($h[0]))] = trim($h[1]);
                if (preg_match("/filename=\"(.*)\"/i", $h[1], $pm)) {
                    $hd['FILE_NAME'] = trim(str_replace('/', '_', $pm[1]));
                } else if (preg_match("/filename=\'(.*)\'/i", $h[1], $pm)) {
                    $hd['FILE_NAME'] = trim(str_replace('/', '_', $pm[1]));
                } else if (preg_match("/filename=(.*)/i", $h[1], $pm)) {
                    $hd['FILE_NAME'] = trim(str_replace('/', '_', $pm[1]));
                }
            }
        }
        return $hd;
    }

    /**
     * 获取后缀名
     * @param $filename
     * @param null $mime
     * @return null
     */
    public static function getSuffix($filename = null, $mime = null)
    {
        $ext = null;
        if ($filename) {
            $ext = explode(".", $filename);
            if (count($ext) < 2) {
                return null;
            }
            $ext = array_pop($ext);
            $ext = strtolower(trim($ext));
        } elseif ($mime) {
            $es = [];
            foreach (self::$mimes as $e => $m) {
                if (is_string($m) && $m === $mime) {
                    $es[] = $e;
                } else if (is_array($m) && in_array($mime, $m)) {
                    $es[] = $e;
                }
            }
            $ext = $es ? current($es) : null;
        }
        return $ext;
    }

    /**
     * 检查是否允许后缀名
     * @param $ext
     * @return bool
     */
    public static function checkExt($ext)
    {
        return in_array($ext, self::$allow_mimes);
    }

    /**
     * 根据 html string dataSource 获得媒体
     * @param $html
     * @return array
     */
    public function getHtmlSource($html)
    {
        if (!$html) {
            return $html;
        }
        $html = str_replace('src =', 'src=', $html);
        $html = explode('src=', $html);
        $src = array();
        foreach ($html as $k => $v) {
            if (strpos($v, 'http') !== false || strpos($v, 'base64') !== false) {
                $v = str_replace('"', "'", $v);
                $v = explode("'", $v);
                foreach ($v as $vk => $vv) {
                    if (strpos($vv, 'http') !== false || strpos($vv, 'base64') !== false) {
                        $src[] = $vv;
                    }
                }
            }
        }
        $src = array_unique($src);
        $new = array();
        foreach ($src as $v) {
            if (strpos($v, 'http') === 0 || strpos($v, 'base64') === false) {
                continue;
            }
            $data = explode(',', $v);
            if (count($data) === 2) {
                $type = $data[0];
                $type = str_replace(['data:', ';base64'], '', $type);
                $data = base64_decode($data[1]);
                list($width, $height) = getimagesize($v);
                $source = imagecreatefromstring($data);
                $new[$v] = array(
                    'ext' => $this->getExtByMime($type),
                    'type' => $type,
                    'data' => $data,
                    'width' => $width,
                    'height' => $height,
                    'source' => $source
                );
            }
        }
        return $new;
    }

    /**
     * 根据数据源、文件后缀名及真实路径，生成真实图片(最优质)
     * @param $source
     * @param $ext
     * @param $path
     * @return void
     */
    public function exportImage($source, $ext, $path)
    {
        switch ($ext) {
            case 'jpg':
            case 'jpeg':
                imagejpeg($source, $path, 100);
                break;
            case 'png':
                imagepng($source, $path, 9);
                break;
            case 'gif':
                imagegif($source, $path);
                break;
            case 'bmp':
            case 'wbmp':
                imagewbmp($source, $path);
                imagewbmp($source, $path);
                break;
        }
    }

    /**
     * 上下翻转就是沿X轴翻转
     * @param $source
     * @return resource
     */
    public function imageScaleY($source)
    {
        $width = imagesx($source);
        $height = imagesy($source);
        $new = imagecreatetruecolor($width, $height);   //创建一个新的图片资源，用来保存沿Y轴翻转后的图片
        for ($y = 0; $y < $height; $y++) {                    //逐条复制图片本身高度，1个像素宽度的图片到新图层
            imagecopy($new, $source, 0, $height - $y - 1, 0, $y, $width, 1);
        }
        imagedestroy($source);
        return $new;
    }

    /**
     * 左右翻转就是沿Y轴翻转
     * @param $source
     * @return resource
     */
    public function imageScaleX($source)
    {
        $width = imagesx($source);
        $height = imagesy($source);
        $new = imagecreatetruecolor($width, $height);   //创建一个新的图片资源，用来保存沿Y轴翻转后的图片
        for ($x = 0; $x < $width; $x++) {                     //逐条复制图片本身高度，1个像素宽度的图片到新图层
            imagecopy($new, $source, $width - $x - 1, 0, $x, 0, 1, $height);
        }
        imagedestroy($source);
        return $new;
    }

}