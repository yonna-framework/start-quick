<?php

namespace Yonna\Throwable\Exception;

abstract class Code
{

    const THROW = 0;

    const DEBUG = 1001;
    const PARAMS = 1002;
    const DATABASE = 1003;
    const SDK = 1004;
    const PERMISSION = 1005;

}