<?php

namespace Yonna\Mapping;

use ReflectionClass;

abstract class Mapping
{

    protected static array $map_data = [];
    protected static array $fetch_cache = [];

    /**
     * 获取Map Data
     * @return array
     */
    public static function getMapData(): array
    {
        $self = new static();
        return $self::$map_data;
    }

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
        $map_data = static::getMapData();
        return isset($map_data[static::class][$value][$optKey])
            ? $map_data[static::class][$value][$optKey] : null;
    }

    /**
     * 设置一个值的label
     * @param $value
     * @param string $label
     */
    protected static function setLabel($value, string $label)
    {
        static::setOptions($value, 'label', $label);
    }

    /**
     * 获取一个值的label
     * @param $value
     * @return string
     */
    public static function getLabel($value): string
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
     * @return array
     */
    public static function fetch(): array
    {
        if (!isset(static::$fetch_cache[static::class])) {
            $objClass = new ReflectionClass(static::class);
            $arrConst = $objClass->getConstants();
            static::$fetch_cache[static::class] = $arrConst;
        }
        return static::$fetch_cache[static::class];
    }

    /**
     * 获取<K,{options}>格式的关联数组
     * @return array
     */
    public static function toMixed()
    {
        return static::getMapData()[static::class];
    }

    /**
     * Value to Array
     * @return array
     */
    public static function toArray(): array
    {
        $arr = static::fetch();
        sort($arr);
        return $arr;
    }

    /**
     * 获取const的json
     * @return string
     */
    public static function toJson()
    {
        $obj = static::fetch();
        return empty($obj) ? json_encode((object)[]) : json_encode($obj);
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
    public static function toKv($target = 'label')
    {
        $data = static::toArray();
        $kv = [];
        foreach ($data as $v) {
            $kv[$v] = static::getOption($v, $target) ?: null;
        }
        return $kv;
    }

    /**
     * 获取<Antd select>格式的数组
     * @param string $target
     * @return array
     */
    public static function toAntd($target = 'label')
    {
        $data = static::toKv($target);
        $kv = [];
        foreach ($data as $k => $v) {
            $kv[] = ['value' => $k, 'label' => $v];
        }
        return $kv;
    }

}