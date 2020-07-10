<?php

namespace Yonna\Throwable\Exception;

abstract class Code
{

    const THROW = 0;

    const DEBUG = 1000;

    const PARAMS = 2001;
    const DATABASE = 2002;
    const SDK = 2003;
    const PERMISSION = 2004;
    const NOT_LOGGING = 2005;
    const TIPS = 2006;

}