<?php

namespace QuickStart\Mapping\User;

use Yonna\Mapping\Mapping;

class Sex extends Mapping
{


    const UN_KNOW = '-1';
    const MAN = '1';
    const WOMEN = '2';

    public function __construct()
    {
        self::setLabel(self::UN_KNOW, '未设置');
        self::setLabel(self::MAN, '男');
        self::setLabel(self::WOMEN, '女');
    }

}