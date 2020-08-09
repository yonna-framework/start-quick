<?php

namespace Yonna\QuickStart\Scope;

use Yonna\QuickStart\Helper\Assets;
use Yonna\QuickStart\Scope\Xoss\Xoss;
use Yonna\Scope\Scope;

abstract class AbstractScope extends Scope
{

    protected function xoss_save($content)
    {
        if (!$content) {
            return $content;
        }
        $src = Assets::getHtmlSource($content);
        foreach ($src['save'] as $k => $v) {
            $res = (new Xoss($this->request()))->saveFile($v);
            $content = str_replace($k, "xoss://{$res['xoss_key']}", $content);
        }
        foreach ($src['http2xoss'] as $k => $v) {
            $content = str_replace($k, "xoss://{$v}", $content);
        }
        return $content;
    }

    protected function xoss_load($content)
    {
        if (!$content) {
            return $content;
        }
        $src = Assets::getHtmlSource($content);
        foreach ($src['xoss'] as $k => $v) {
            $key = str_replace('xoss://', '', $v);
            $content = str_replace($v, $this->request()->getHost() . '?scope=xoss_download&k=' . $key, $content);
        }
        return $content;
    }

}