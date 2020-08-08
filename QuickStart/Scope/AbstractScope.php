<?php

namespace Yonna\QuickStart\Scope;

use Yonna\QuickStart\Helper\Assets;
use Yonna\QuickStart\Scope\Xoss\Xoss;
use Yonna\Scope\Scope;

abstract class AbstractScope extends Scope
{

    protected function xoss($content)
    {
        if (!$content) {
            return $content;
        }
        $src = Assets::getHtmlSource($content);
        foreach ($src as $k => $v) {
            $res = (new Xoss($this->request()))->saveFile($v);
            $content = str_replace($k, $this->request()->getHost() . '?scope=xoss_download&k=' . $res['xoss_key'], $content);
        }
        return $content;
    }

}