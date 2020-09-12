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
            $content = str_replace($k, Xoss::ASSET . $res['xoss_key'], $content);
        }
        return $content;
    }

}