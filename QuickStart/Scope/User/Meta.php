<?php

namespace Yonna\QuickStart\Scope\User;

use Yonna\QuickStart\Mapping\Common\Boolean;
use Yonna\QuickStart\Mapping\User\MetaValueFormat;
use Yonna\QuickStart\Scope\AbstractScope;
use Yonna\Database\DB;
use Yonna\Database\Driver\Pdo\Where;
use Yonna\Throwable\Exception;
use Yonna\Validator\ArrayValidator;

/**
 * Class Meta
 * @package Yonna\QuickStart\Scope\User
 */
class Meta extends AbstractScope
{

    /**
     * @return mixed
     * @throws Exception\DatabaseException
     */
    public function category(): array
    {
        return DB::connect()->table('user_meta_category')
            ->field('key,value_format,value_default')
            ->where(fn(Where $w) => $w->equalTo('status', Boolean::true))
            ->orderBy('ordering', 'desc')
            ->multi();
    }

    /**
     * @return int
     * @throws Exception\DatabaseException
     */
    public function addCategory()
    {
        ArrayValidator::required($this->input(), ['key', 'value_format'], function ($error) {
            Exception::throw($error);
        });
        $add = [
            'key' => $this->input('key'),
            'value_format' => $this->input('value_format'),
            'value_default' => $this->input('value_default') ?? '',
            'status' => $this->input('status') ?? Boolean::false,
            'ordering' => $this->input('ordering') ?? 0,
        ];
        return DB::connect()->table('user_meta_category')->insert($add);
    }

    /**
     * @return mixed
     * @throws Exception\DatabaseException
     */
    public function me()
    {
        $values = DB::connect()->table('user_meta')
            ->field('key,value')
            ->where(fn(Where $w) => $w->equalTo('user_id', $this->request()->getLoggingId()))
            ->multi();
        $category = $this->category();
        $meta = [];
        foreach ($category as $c) {
            $k = $c['user_meta_category_key'];
            $v = $values[$k] ?? $c['user_meta_category_value_default'];
            switch ($c['user_meta_category_value_format']) {
                case MetaValueFormat::INTEGER:
                    $v = $v ? (int)$v : 0;
                    break;
                case MetaValueFormat::FLOAT1:
                    $v = $v ? round($v, 1) : 0.0;
                    break;
                case MetaValueFormat::FLOAT2:
                    $v = $v ? round($v, 2) : 0.00;
                    break;
                case MetaValueFormat::FLOAT3:
                    $v = $v ? round($v, 3) : 0.000;
                    break;
                case MetaValueFormat::DATE:
                    if (is_numeric($v)) {
                        $v = date('Y-m-d', $v);
                    } else {
                        $v = $v ? $v : '1970-01-01';
                    }
                    break;
                case MetaValueFormat::TIME:
                    if (is_numeric($v)) {
                        $v = date('H:i:s', $v);
                    } else {
                        $v = $v ? $v : '00:00:00';
                    }
                    break;
                case MetaValueFormat::DATETIME:
                    if (is_numeric($v)) {
                        $v = date('Y-m-d H:i:s', $v);
                    } else {
                        $v = $v ? $v : '1970-01-01 00:00:00';
                    }
                    break;
                case MetaValueFormat::STRING:
                default:
                    $v = $v ? (string)$v : '';
                    break;
            }
            $meta['user_meta_' . $k] = $v;
        }
        return $meta;
    }

}