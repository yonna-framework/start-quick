<?php

namespace Yonna\Mapping;

use Exception;
use ReflectionClass;
use ReflectionException;

abstract class Mapping
{

    protected static array $map_data = [];
    protected static array $fetch_cache = [];

    /**
     * 设置一个值的自定义参数
     * @param $value
     * @param $optKey
     * @param $optVal
     */
    protected static function setOptions($value, $optKey, $optVal)
    {
        if (!isset(static::$map_data[static::class])) {
            static::$map_data[static::class] = [];
        }
        static::$map_data[static::class][$value][$optKey] = $optVal;
    }

    /**
     * 获取一个值的自定义参数
     * @param $value
     * @param $optKey
     * @return mixed
     */
    public static function getOption($value, $optKey)
    {
        $self = new static();
        $map_data = $self::$map_data;
        return isset($map_data[static::class][$value][$optKey])
            ? $map_data[static::class][$value][$optKey] : null;
    }

    /**
     * 设置一个值的label
     * @param $value
     * @param $label
     */
    protected static function setLabel($value, $label)
    {
        static::setOptions($value, 'label', $label);
    }

    /**
     * 获取一个值的label
     * @param $value
     * @return string
     */
    public static function getLabel($value)
    {
        return static::getOption($value, 'label') ?: '';
    }

    /**
     * 设置一个值的status
     * @param $value
     * @param $status
     */
    protected static function setStatus($value, $status)
    {
        static::setOptions($value, 'status', $status);
    }

    /**
     * 设置一个值的status
     * @param $value
     * @return string
     */
    public static function getStatus($value)
    {
        return static::getOption($value, 'status') ?: '1';
    }

    /**
     * 反射mapping的数据
     * @return mixed
     * @throws Exception
     */
    public static function fetch()
    {
        if (!isset(static::$fetch_cache[static::class])) {
            try {
                $objClass = new ReflectionClass(static::class);
                $arrConst = $objClass->getConstants();
                static::$fetch_cache[static::class] = $arrConst;
            } catch (ReflectionException $e) {
                throw new Exception($e->getMessage());
            }
        }
        return static::$fetch_cache[static::class] ?? [];
    }

    /**
     * Value to Array
     * @return mixed
     */
    public static function toArray()
    {
        $arr = [];
        try {
            $arr = static::fetch();
            sort($arr);
        } catch (Exception $e) {
            //
        }
        return $arr;
    }

    /**
     * 获取const的json
     * @return string
     */
    public static function toJson()
    {
        $obj = (object)[];
        try {
            $obj = static::fetch();
        } catch (Exception $e) {
            //
        }
        return json_encode($obj);
    }

    /**
     * 获取值的逗号序列
     * @return string
     */
    public static function toComma()
    {
        return implode(',', static::toArray() ?: []);
    }

    /**
     * 获取<K,V>格式的关联数组
     * @param string $target
     * @return array
     */
    public static function toKV($target = 'label')
    {
        $data = static::toArray();
        $kv = [];
        foreach ($data as $v) {
            $kv[$v] = static::getOption($v, $target) ?: null;
        }
        return $kv;
    }

    /**
     * 获取<K,{options}>格式的关联数组
     * @return array
     */
    public static function toMixed()
    {
        $data = static::toArray();
        $kv = [];
        foreach ($data as $v) {
            $kv[$v] = static::$map_data[static::class][$v];
        }
        return $kv;
    }

}