<?php

namespace Yonna\Validator;

use Closure;
use Yonna\Foundation\Arr;

class ArrayValidator extends AbstractValidator
{

    /**
     * check params is set
     * @param array $data check witch data
     * @param array $keys
     * @param Closure $failCallback
     */
    public static function required(array $data, array $keys, Closure $failCallback): void
    {
        if (empty($keys)) return;
        $checker = self::createChecker();
        if (empty($data)) {
            $checker->error('data is empty for check');
        }
        foreach ($keys as $k) {
            if (!Arr::get($data, $k)) {
                $checker->error("{$k} unset");
            }
        }
        $checker->callback($failCallback);
    }

    /**
     * check params is numeric
     * @param array $data check witch data
     * @param array $keys
     * @param float $min
     * @param float $max
     * @param Closure $failCallback
     */
    public static function numeric(array $data, array $keys, float $min, float $max, Closure $failCallback): void
    {
        if (empty($keys)) return;
        $checker = self::createChecker();
        if (empty($data)) {
            $checker->error('data is empty for check');
        }
        foreach ($keys as $k) {
            $val = Arr::get($data, $k);
            if (!is_numeric($val)) {
                $checker->error("{$k} is not a numeric");
            } else if ($val < $min) {
                $checker->error("{$k} is less than {$min}");
            } else if ($val > $max) {
                $checker->error("{$k} is greater than {$max}");
            }
        }
        $checker->callback($failCallback);
    }

    /**
     * check params is integer
     * @param array $data check witch data
     * @param array $keys
     * @param int $min
     * @param int $max
     * @param Closure $failCallback
     */
    public static function integer(array $data, array $keys, int $min, int $max, Closure $failCallback): void
    {
        if (empty($keys)) return;
        $checker = self::createChecker();
        if (empty($data)) {
            $checker->error('data is empty for check');
        }
        foreach ($keys as $k) {
            $val = Arr::get($data, $k);
            $val2 = floor($val);
            if ($val2 != $val) {
                $checker->error("{$k} is not integer");
            } else if ($val2 < $min) {
                $checker->error("{$k} is less than {$min}");
            } else if ($val2 > $max) {
                $checker->error("{$k} is greater than {$max}");
            }
        }
        $checker->callback($failCallback);
    }

    /**
     * check params is numeric
     * @param array $data check witch data
     * @param array $keys
     * @param Closure $failCallback
     */
    public static function array(array $data, array $keys, Closure $failCallback): void
    {
        if (empty($keys)) return;
        $checker = self::createChecker();
        if (empty($data)) {
            $checker->error('data is empty for check');
        }
        foreach ($keys as $k) {
            if (!is_array(Arr::get($data, $k))) {
                $checker->error("{$k} is not a array");
            }
        }
        $checker->callback($failCallback);
    }

    /**
     * combine keys by all
     * @param array $data
     * @param array $keys
     * @param Closure $failCallback
     * @see slow
     */
    public static function multiple(array $data, array $keys, Closure $failCallback): void
    {
        if (empty($rules)) return;
        $checker = self::createChecker();
        if (empty($data)) {
            $checker->error('data is empty for check');
        }
        foreach ($keys as $k => $setting) {
            $val = Arr::get($data, $k);
            foreach ($setting as $act => $req) {
                switch ($act) {
                    case 'required':
                        if ($req) {
                            if (!Arr::get($data, $k)) {
                                $checker->error("{$k} unset");
                            }
                        }
                        break;
                    case 'type':
                        switch ($req) {
                            case 'numeric':
                            case 'number':
                            case 'float':
                                $min = $req['min'] ?? 0;
                                $max = $req['max'] ?? 99999;
                                if (!is_numeric($val)) {
                                    $checker->error("{$k} is not a numeric");
                                } else if ($val < $min) {
                                    $checker->error("{$k} is less than {$min}");
                                } else if ($val > $max) {
                                    $checker->error("{$k} is greater than {$max}");
                                }
                                break;
                            case 'integer':
                            case 'int':
                                $val2 = floor($val);
                                $min = $req['min'] ?? 0;
                                $max = $req['max'] ?? 99999;
                                if ($val2 != $val) {
                                    $checker->error("{$k} is not integer");
                                } else if ($val2 < $min) {
                                    $checker->error("{$k} is less than {$min}");
                                } else if ($val2 > $max) {
                                    $checker->error("{$k} is greater than {$max}");
                                }
                                break;
                            case 'array':
                                if (!is_array($val)) {
                                    $checker->error("{$k} is not a array");
                                }
                                break;
                            default:
                                break;
                        }
                        break;
                    default:
                        break;
                }
            }
        }
        $checker->callback($failCallback);
    }

}