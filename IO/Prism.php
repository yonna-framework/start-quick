<?php
/**
 * 棱镜
 * 可帮助获取input参数
 */

namespace Yonna\IO;

use ReflectionClass;
use ReflectionException;

class Prism
{

    /**
     * Prism constructor.
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $input = $request->getInput();
        $reflect = new ReflectionClass(get_called_class());
        $properties = $reflect->getProperties();
        foreach ($properties as $p) {
            $opt = $p->name;
            if (isset($input[$opt])) {
                $this->$opt = $input[$opt];
            }
        }
    }

}