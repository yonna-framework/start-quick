<?php

namespace Yonna\QuickStart\Mapping\User;

use Yonna\Mapping\Mapping;

class MetaComponent extends Mapping
{

    const INPUT_STRING = 'input_string';
    const INPUT_INTEGER = 'input_integer';
    const INPUT_FLOAT = 'input_float';
    const INPUT_PASSWORD = 'input_password';
    const PICKER_DATE = 'picker_date';
    const PICKER_DATETIME = 'picker_datetime';
    const PICKER_TIME = 'picker_time';
    const CASCADER = 'cascader';
    const CASCADER_REGION = 'cascader_region';
    const SELECT = 'select';
    const CHECKBOX = 'checkbox';
    const RADIO = 'radio';


    public function __construct()
    {
        self::setLabel(self::INPUT_STRING, 'input_string');
        self::setLabel(self::INPUT_INTEGER, 'input_integer');
        self::setLabel(self::INPUT_FLOAT, 'input_float');
        self::setLabel(self::INPUT_PASSWORD, 'input_password');
        self::setLabel(self::PICKER_DATE, 'picker_date');
        self::setLabel(self::PICKER_DATETIME, 'picker_datetime');
        self::setLabel(self::PICKER_TIME, 'picker_time');
        self::setLabel(self::CASCADER, 'cascader');
        self::setLabel(self::CASCADER_REGION, 'cascader_region');
        self::setLabel(self::SELECT, 'select');
        self::setLabel(self::CHECKBOX, 'checkbox');
        self::setLabel(self::RADIO, 'radio');
    }

}