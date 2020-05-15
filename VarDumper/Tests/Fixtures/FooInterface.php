<?php

namespace Yonna\VarDumper\Tests\Fixtures;

interface FooInterface
{
    /**
     * Hello.
     */
    public function foo(?\stdClass $a, \stdClass $b = null);
}
