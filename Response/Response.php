<?php
/**
 * Yonna Response
 * @author hunzsig
 */

namespace Yonna\Response;

use Throwable;
use Yonna\Response\Consequent;

/**
 * Class Response
 * @package Core\Core
 */
class Response
{

    /**
     * 处理 args
     * @param array $args
     * @return array
     */
    private static function args(array $args)
    {
        if (empty($args)) {
            return [];
        }
        foreach ($args as &$a) {
            if (is_array($a)) {
                $a = self::args($a);
            } else if (is_object($a)) {
                $a = '[object]' . get_class($a);
            }
        }
        return $args;
    }

    /**
     * safety debug backtrace
     * @param array $trace
     * * @param bool $safe 是否安全模式
     * @return array
     */
    private static function debug_backtrace(array $trace, bool $safe = true)
    {
        $path = realpath(__DIR__ . '/../../../..');
        if (!$trace) {
            return [];
        }
        foreach ($trace as $tk => $t) {
            if ($safe === true) {
                if (isset($t['line'])) unset($trace[$tk]['line']);
                if (isset($t['type'])) unset($trace[$tk]['type']);
                if (isset($t['object'])) unset($trace[$tk]['object']);
                if (isset($t['args'])) unset($trace[$tk]['args']);
                if (!empty($t['file'])) {
                    if (strpos($t['file'], 'java://') === 0) {
                        $trace[$tk]['file'] = 'jar package';
                    } else {
                        $trace[$tk]['file'] = str_replace($path, '#:Yonna', str_replace(
                            'vendor' . DIRECTORY_SEPARATOR . 'yonna' . DIRECTORY_SEPARATOR,
                            '',
                            $t['file']
                        ));
                    }
                }
            } else {
                $trace[$tk]['args'] = self::args($t['args'] ?? []);
                if (!empty($t['file']) && strpos($t['file'], 'java://') === 0) {
                    $trace[$tk]['file'] = 'jar package';
                }
            }
        }
        return $trace;
    }

    /**
     * @param $Collector
     * @return false|string
     */
    public static function handle(Collector $Collector)
    {
        return $Collector->response();
    }

    public static function throwable(Throwable $t, $type = 'json', $charset = 'utf-8')
    {
        $HandleCollector = new Collector();
        $HandleCollector
            ->setResponseDataType($type)
            ->setCharset($charset)
            ->setCode(Code::THROWABLE)
            ->setMsg($t->getMessage())
            ->setData([
                'error_code' => $t->getCode(),
                'error_trace' => self::debug_backtrace($t->getTrace(), getenv('DEBUG') !== 'true')
            ]);
        return $HandleCollector;
    }

    public static function error(Throwable $t, array $data = array(), $type = 'json', $charset = 'utf-8')
    {
        $HandleCollector = new Collector();
        $HandleCollector
            ->setResponseDataType($type)
            ->setCharset($charset)
            ->setCode(Code::ERROR)
            ->setMsg($t->getMessage())
            ->setData([
                'error_code' => $t->getCode(),
                'error_data' => $data,
                'error_trace' => self::debug_backtrace($t->getTrace(), getenv('DEBUG') !== 'true')
            ]);
        return $HandleCollector;
    }

    public static function success(string $msg = 'success', array $data = array(), $type = 'json', $charset = 'utf-8')
    {
        $HandleCollector = new Collector();
        $HandleCollector
            ->setResponseDataType($type)
            ->setCharset($charset)
            ->setCode(Code::SUCCESS)
            ->setMsg($msg)
            ->setData($data);
        return $HandleCollector;
    }

    public static function broadcast(string $msg = 'broadcast', array $data = array(), $type = 'json', $charset = 'utf-8')
    {
        $HandleCollector = new Collector();
        $HandleCollector
            ->setResponseDataType($type)
            ->setCharset($charset)
            ->setCode(Code::BROADCAST)
            ->setMsg($msg)
            ->setData($data);
        return $HandleCollector;
    }

    public static function goon(string $msg = 'goon', array $data = array(), $type = 'json', $charset = 'utf-8')
    {
        $HandleCollector = new Collector();
        $HandleCollector
            ->setResponseDataType($type)
            ->setCharset($charset)
            ->setCode(Code::GOON)
            ->setMsg($msg)
            ->setData($data);
        return $HandleCollector;
    }

    public static function abort(string $msg = 'abort', array $data = array(), $type = 'json', $charset = 'utf-8')
    {
        $HandleCollector = new Collector();
        $HandleCollector
            ->setResponseDataType($type)
            ->setCharset($charset)
            ->setCode(Code::ABORT)
            ->setMsg($msg)
            ->setData($data);
        return $HandleCollector;
    }

    public static function notPermission(string $msg = 'not permission', array $data = array(), $type = 'json', $charset = 'utf-8')
    {
        $HandleCollector = new Collector();
        $HandleCollector
            ->setResponseDataType($type)
            ->setCharset($charset)
            ->setCode(Code::NOT_PERMISSION)
            ->setMsg($msg)
            ->setData($data);
        return $HandleCollector;
    }

    public static function notLogging(string $msg = 'not logging', array $data = array(), $type = 'json', $charset = 'utf-8')
    {
        $HandleCollector = new Collector();
        $HandleCollector
            ->setResponseDataType($type)
            ->setCharset($charset)
            ->setCode(Code::NOT_LOGGING)
            ->setMsg($msg)
            ->setData($data);
        return $HandleCollector;
    }

    public static function notFound(string $msg = 'not found', array $data = array(), $type = 'json', $charset = 'utf-8')
    {
        $HandleCollector = new Collector();
        $HandleCollector
            ->setResponseDataType($type)
            ->setCharset($charset)
            ->setCode(Code::NOT_FOUND)
            ->setMsg($msg)
            ->setData($data);
        return $HandleCollector;
    }

    public static function download(Consequent\File $data = null)
    {
        $HandleCollector = new Collector();
        $HandleCollector
            ->setResponseDataType('file')
            ->setData($data);
        return $HandleCollector;
    }


}