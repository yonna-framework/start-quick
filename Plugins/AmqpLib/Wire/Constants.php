<?php

namespace AmqpLib\Wire;

abstract class Constants
{
    const VERSION = '';
    const AMQP_HEADER = '';

    /**
     * @var array<int, string>
     */
    protected static $FRAME_TYPES = [];

    /**
     * @var array<int, string>
     */
    protected static $CONTENT_METHODS = [];

    /**
     * @var array<int, string>
     */
    protected static $CLOSE_METHODS = [];

    /**
     * @var array<string, string>
     */
    public static $GLOBAL_METHOD_NAMES = [];

    /**
     * @return string
     */
    public function getHeader()
    {
        return static::AMQP_HEADER;
    }

    /**
     * @param int $type
     * @return bool
     */
    public function isFrameType($type)
    {
        return array_key_exists($type, static::$FRAME_TYPES);
    }

    /**
     * @param int $type
     * @return string
     */
    public function getFrameType($type)
    {
        return static::$FRAME_TYPES[$type];
    }

    /**
     * @param string $method
     * @return bool
     */
    public function isContentMethod($method)
    {
        return in_array($method, static::$CONTENT_METHODS, false);
    }

    /**
     * @param string $method
     * @return bool
     */
    public function isCloseMethod($method)
    {
        return in_array($method, static::$CLOSE_METHODS, false);
    }
}
