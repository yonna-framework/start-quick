<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yonna\VarDumper\Cloner;

use Yonna\VarDumper\Caster\Caster;
use Yonna\VarDumper\Exception\ThrowingCasterException;

/**
 * AbstractCloner implements a generic caster mechanism for objects and resources.
 *
 * @author Nicolas Grekas <p@tchwork.com>
 */
abstract class AbstractCloner implements ClonerInterface
{
    public static $defaultCasters = [
        '__PHP_Incomplete_Class' => ['Yonna\VarDumper\Caster\Caster', 'castPhpIncompleteClass'],

        'Yonna\VarDumper\Caster\CutStub' => ['Yonna\VarDumper\Caster\StubCaster', 'castStub'],
        'Yonna\VarDumper\Caster\CutArrayStub' => ['Yonna\VarDumper\Caster\StubCaster', 'castCutArray'],
        'Yonna\VarDumper\Caster\ConstStub' => ['Yonna\VarDumper\Caster\StubCaster', 'castStub'],
        'Yonna\VarDumper\Caster\EnumStub' => ['Yonna\VarDumper\Caster\StubCaster', 'castEnum'],

        'Closure' => ['Yonna\VarDumper\Caster\ReflectionCaster', 'castClosure'],
        'Generator' => ['Yonna\VarDumper\Caster\ReflectionCaster', 'castGenerator'],
        'ReflectionType' => ['Yonna\VarDumper\Caster\ReflectionCaster', 'castType'],
        'ReflectionGenerator' => ['Yonna\VarDumper\Caster\ReflectionCaster', 'castReflectionGenerator'],
        'ReflectionClass' => ['Yonna\VarDumper\Caster\ReflectionCaster', 'castClass'],
        'ReflectionFunctionAbstract' => ['Yonna\VarDumper\Caster\ReflectionCaster', 'castFunctionAbstract'],
        'ReflectionMethod' => ['Yonna\VarDumper\Caster\ReflectionCaster', 'castMethod'],
        'ReflectionParameter' => ['Yonna\VarDumper\Caster\ReflectionCaster', 'castParameter'],
        'ReflectionProperty' => ['Yonna\VarDumper\Caster\ReflectionCaster', 'castProperty'],
        'ReflectionReference' => ['Yonna\VarDumper\Caster\ReflectionCaster', 'castReference'],
        'ReflectionExtension' => ['Yonna\VarDumper\Caster\ReflectionCaster', 'castExtension'],
        'ReflectionZendExtension' => ['Yonna\VarDumper\Caster\ReflectionCaster', 'castZendExtension'],

        'Doctrine\Common\Persistence\ObjectManager' => ['Yonna\VarDumper\Caster\StubCaster', 'cutInternals'],
        'Doctrine\Common\Proxy\Proxy' => ['Yonna\VarDumper\Caster\DoctrineCaster', 'castCommonProxy'],
        'Doctrine\ORM\Proxy\Proxy' => ['Yonna\VarDumper\Caster\DoctrineCaster', 'castOrmProxy'],
        'Doctrine\ORM\PersistentCollection' => ['Yonna\VarDumper\Caster\DoctrineCaster', 'castPersistentCollection'],

        'DOMException' => ['Yonna\VarDumper\Caster\DOMCaster', 'castException'],
        'DOMStringList' => ['Yonna\VarDumper\Caster\DOMCaster', 'castLength'],
        'DOMNameList' => ['Yonna\VarDumper\Caster\DOMCaster', 'castLength'],
        'DOMImplementation' => ['Yonna\VarDumper\Caster\DOMCaster', 'castImplementation'],
        'DOMImplementationList' => ['Yonna\VarDumper\Caster\DOMCaster', 'castLength'],
        'DOMNode' => ['Yonna\VarDumper\Caster\DOMCaster', 'castNode'],
        'DOMNameSpaceNode' => ['Yonna\VarDumper\Caster\DOMCaster', 'castNameSpaceNode'],
        'DOMDocument' => ['Yonna\VarDumper\Caster\DOMCaster', 'castDocument'],
        'DOMNodeList' => ['Yonna\VarDumper\Caster\DOMCaster', 'castLength'],
        'DOMNamedNodeMap' => ['Yonna\VarDumper\Caster\DOMCaster', 'castLength'],
        'DOMCharacterData' => ['Yonna\VarDumper\Caster\DOMCaster', 'castCharacterData'],
        'DOMAttr' => ['Yonna\VarDumper\Caster\DOMCaster', 'castAttr'],
        'DOMElement' => ['Yonna\VarDumper\Caster\DOMCaster', 'castElement'],
        'DOMText' => ['Yonna\VarDumper\Caster\DOMCaster', 'castText'],
        'DOMTypeinfo' => ['Yonna\VarDumper\Caster\DOMCaster', 'castTypeinfo'],
        'DOMDomError' => ['Yonna\VarDumper\Caster\DOMCaster', 'castDomError'],
        'DOMLocator' => ['Yonna\VarDumper\Caster\DOMCaster', 'castLocator'],
        'DOMDocumentType' => ['Yonna\VarDumper\Caster\DOMCaster', 'castDocumentType'],
        'DOMNotation' => ['Yonna\VarDumper\Caster\DOMCaster', 'castNotation'],
        'DOMEntity' => ['Yonna\VarDumper\Caster\DOMCaster', 'castEntity'],
        'DOMProcessingInstruction' => ['Yonna\VarDumper\Caster\DOMCaster', 'castProcessingInstruction'],
        'DOMXPath' => ['Yonna\VarDumper\Caster\DOMCaster', 'castXPath'],

        'XMLReader' => ['Yonna\VarDumper\Caster\XmlReaderCaster', 'castXmlReader'],

        'ErrorException' => ['Yonna\VarDumper\Caster\ExceptionCaster', 'castErrorException'],
        'Exception' => ['Yonna\VarDumper\Caster\ExceptionCaster', 'castException'],
        'Error' => ['Yonna\VarDumper\Caster\ExceptionCaster', 'castError'],
        'Symfony\Component\DependencyInjection\ContainerInterface' => ['Yonna\VarDumper\Caster\StubCaster', 'cutInternals'],
        'Symfony\Component\HttpClient\CurlHttpClient' => ['Yonna\VarDumper\Caster\SymfonyCaster', 'castHttpClient'],
        'Symfony\Component\HttpClient\NativeHttpClient' => ['Yonna\VarDumper\Caster\SymfonyCaster', 'castHttpClient'],
        'Symfony\Component\HttpClient\Response\CurlResponse' => ['Yonna\VarDumper\Caster\SymfonyCaster', 'castHttpClient'],
        'Symfony\Component\HttpClient\Response\NativeResponse' => ['Yonna\VarDumper\Caster\SymfonyCaster', 'castHttpClient'],
        'Symfony\Component\HttpFoundation\Request' => ['Yonna\VarDumper\Caster\SymfonyCaster', 'castRequest'],
        'Yonna\VarDumper\Exception\ThrowingCasterException' => ['Yonna\VarDumper\Caster\ExceptionCaster', 'castThrowingCasterException'],
        'Yonna\VarDumper\Caster\TraceStub' => ['Yonna\VarDumper\Caster\ExceptionCaster', 'castTraceStub'],
        'Yonna\VarDumper\Caster\FrameStub' => ['Yonna\VarDumper\Caster\ExceptionCaster', 'castFrameStub'],
        'Symfony\Component\Debug\Exception\SilencedErrorContext' => ['Yonna\VarDumper\Caster\ExceptionCaster', 'castSilencedErrorContext'],

        'ProxyManager\Proxy\ProxyInterface' => ['Yonna\VarDumper\Caster\ProxyManagerCaster', 'castProxy'],
        'PHPUnit_Framework_MockObject_MockObject' => ['Yonna\VarDumper\Caster\StubCaster', 'cutInternals'],
        'Prophecy\Prophecy\ProphecySubjectInterface' => ['Yonna\VarDumper\Caster\StubCaster', 'cutInternals'],
        'Mockery\MockInterface' => ['Yonna\VarDumper\Caster\StubCaster', 'cutInternals'],

        'PDO' => ['Yonna\VarDumper\Caster\PdoCaster', 'castPdo'],
        'PDOStatement' => ['Yonna\VarDumper\Caster\PdoCaster', 'castPdoStatement'],

        'AMQPConnection' => ['Yonna\VarDumper\Caster\AmqpCaster', 'castConnection'],
        'AMQPChannel' => ['Yonna\VarDumper\Caster\AmqpCaster', 'castChannel'],
        'AMQPQueue' => ['Yonna\VarDumper\Caster\AmqpCaster', 'castQueue'],
        'AMQPExchange' => ['Yonna\VarDumper\Caster\AmqpCaster', 'castExchange'],
        'AMQPEnvelope' => ['Yonna\VarDumper\Caster\AmqpCaster', 'castEnvelope'],

        'ArrayObject' => ['Yonna\VarDumper\Caster\SplCaster', 'castArrayObject'],
        'ArrayIterator' => ['Yonna\VarDumper\Caster\SplCaster', 'castArrayIterator'],
        'SplDoublyLinkedList' => ['Yonna\VarDumper\Caster\SplCaster', 'castDoublyLinkedList'],
        'SplFileInfo' => ['Yonna\VarDumper\Caster\SplCaster', 'castFileInfo'],
        'SplFileObject' => ['Yonna\VarDumper\Caster\SplCaster', 'castFileObject'],
        'SplFixedArray' => ['Yonna\VarDumper\Caster\SplCaster', 'castFixedArray'],
        'SplHeap' => ['Yonna\VarDumper\Caster\SplCaster', 'castHeap'],
        'SplObjectStorage' => ['Yonna\VarDumper\Caster\SplCaster', 'castObjectStorage'],
        'SplPriorityQueue' => ['Yonna\VarDumper\Caster\SplCaster', 'castHeap'],
        'OuterIterator' => ['Yonna\VarDumper\Caster\SplCaster', 'castOuterIterator'],
        'WeakReference' => ['Yonna\VarDumper\Caster\SplCaster', 'castWeakReference'],

        'Redis' => ['Yonna\VarDumper\Caster\RedisCaster', 'castRedis'],
        'RedisArray' => ['Yonna\VarDumper\Caster\RedisCaster', 'castRedisArray'],
        'RedisCluster' => ['Yonna\VarDumper\Caster\RedisCaster', 'castRedisCluster'],

        'DateTimeInterface' => ['Yonna\VarDumper\Caster\DateCaster', 'castDateTime'],
        'DateInterval' => ['Yonna\VarDumper\Caster\DateCaster', 'castInterval'],
        'DateTimeZone' => ['Yonna\VarDumper\Caster\DateCaster', 'castTimeZone'],
        'DatePeriod' => ['Yonna\VarDumper\Caster\DateCaster', 'castPeriod'],

        'GMP' => ['Yonna\VarDumper\Caster\GmpCaster', 'castGmp'],

        'MessageFormatter' => ['Yonna\VarDumper\Caster\IntlCaster', 'castMessageFormatter'],
        'NumberFormatter' => ['Yonna\VarDumper\Caster\IntlCaster', 'castNumberFormatter'],
        'IntlTimeZone' => ['Yonna\VarDumper\Caster\IntlCaster', 'castIntlTimeZone'],
        'IntlCalendar' => ['Yonna\VarDumper\Caster\IntlCaster', 'castIntlCalendar'],
        'IntlDateFormatter' => ['Yonna\VarDumper\Caster\IntlCaster', 'castIntlDateFormatter'],

        'Memcached' => ['Yonna\VarDumper\Caster\MemcachedCaster', 'castMemcached'],

        'Ds\Collection' => ['Yonna\VarDumper\Caster\DsCaster', 'castCollection'],
        'Ds\Map' => ['Yonna\VarDumper\Caster\DsCaster', 'castMap'],
        'Ds\Pair' => ['Yonna\VarDumper\Caster\DsCaster', 'castPair'],
        'Yonna\VarDumper\Caster\DsPairStub' => ['Yonna\VarDumper\Caster\DsCaster', 'castPairStub'],

        ':curl' => ['Yonna\VarDumper\Caster\ResourceCaster', 'castCurl'],
        ':dba' => ['Yonna\VarDumper\Caster\ResourceCaster', 'castDba'],
        ':dba persistent' => ['Yonna\VarDumper\Caster\ResourceCaster', 'castDba'],
        ':gd' => ['Yonna\VarDumper\Caster\ResourceCaster', 'castGd'],
        ':mysql link' => ['Yonna\VarDumper\Caster\ResourceCaster', 'castMysqlLink'],
        ':pgsql large object' => ['Yonna\VarDumper\Caster\PgSqlCaster', 'castLargeObject'],
        ':pgsql link' => ['Yonna\VarDumper\Caster\PgSqlCaster', 'castLink'],
        ':pgsql link persistent' => ['Yonna\VarDumper\Caster\PgSqlCaster', 'castLink'],
        ':pgsql result' => ['Yonna\VarDumper\Caster\PgSqlCaster', 'castResult'],
        ':process' => ['Yonna\VarDumper\Caster\ResourceCaster', 'castProcess'],
        ':stream' => ['Yonna\VarDumper\Caster\ResourceCaster', 'castStream'],
        ':OpenSSL X.509' => ['Yonna\VarDumper\Caster\ResourceCaster', 'castOpensslX509'],
        ':persistent stream' => ['Yonna\VarDumper\Caster\ResourceCaster', 'castStream'],
        ':stream-context' => ['Yonna\VarDumper\Caster\ResourceCaster', 'castStreamContext'],
        ':xml' => ['Yonna\VarDumper\Caster\XmlResourceCaster', 'castXml'],
    ];

    protected $maxItems = 2500;
    protected $maxString = -1;
    protected $minDepth = 1;

    private $casters = [];
    private $prevErrorHandler;
    private $classInfo = [];
    private $filter = 0;

    /**
     * @param callable[]|null $casters A map of casters
     *
     * @see addCasters
     */
    public function __construct(array $casters = null)
    {
        if (null === $casters) {
            $casters = static::$defaultCasters;
        }
        $this->addCasters($casters);
    }

    /**
     * Adds casters for resources and objects.
     *
     * Maps resources or objects types to a callback.
     * Types are in the key, with a callable caster for value.
     * Resource types are to be prefixed with a `:`,
     * see e.g. static::$defaultCasters.
     *
     * @param callable[] $casters A map of casters
     */
    public function addCasters(array $casters)
    {
        foreach ($casters as $type => $callback) {
            $closure = &$this->casters[$type][];
            $closure = $callback instanceof \Closure ? $callback : static function (...$args) use ($callback, &$closure) {
                return ($closure = \Closure::fromCallable($callback))(...$args);
            };
        }
    }

    /**
     * Sets the maximum number of items to clone past the minimum depth in nested structures.
     *
     * @param int $maxItems
     */
    public function setMaxItems($maxItems)
    {
        $this->maxItems = (int) $maxItems;
    }

    /**
     * Sets the maximum cloned length for strings.
     *
     * @param int $maxString
     */
    public function setMaxString($maxString)
    {
        $this->maxString = (int) $maxString;
    }

    /**
     * Sets the minimum tree depth where we are guaranteed to clone all the items.  After this
     * depth is reached, only setMaxItems items will be cloned.
     *
     * @param int $minDepth
     */
    public function setMinDepth($minDepth)
    {
        $this->minDepth = (int) $minDepth;
    }

    /**
     * Clones a PHP variable.
     *
     * @param mixed $var    Any PHP variable
     * @param int   $filter A bit field of Caster::EXCLUDE_* constants
     *
     * @return Data The cloned variable represented by a Data object
     */
    public function cloneVar($var, $filter = 0)
    {
        $this->prevErrorHandler = set_error_handler(function ($type, $msg, $file, $line, $context = []) {
            if (E_RECOVERABLE_ERROR === $type || E_USER_ERROR === $type) {
                // Cloner never dies
                throw new \ErrorException($msg, 0, $type, $file, $line);
            }

            if ($this->prevErrorHandler) {
                return ($this->prevErrorHandler)($type, $msg, $file, $line, $context);
            }

            return false;
        });
        $this->filter = $filter;

        if ($gc = gc_enabled()) {
            gc_disable();
        }
        try {
            return new Data($this->doClone($var));
        } finally {
            if ($gc) {
                gc_enable();
            }
            restore_error_handler();
            $this->prevErrorHandler = null;
        }
    }

    /**
     * Effectively clones the PHP variable.
     *
     * @param mixed $var Any PHP variable
     *
     * @return array The cloned variable represented in an array
     */
    abstract protected function doClone($var);

    /**
     * Casts an object to an array representation.
     *
     * @param Stub $stub     The Stub for the casted object
     * @param bool $isNested True if the object is nested in the dumped structure
     *
     * @return array The object casted as array
     */
    protected function castObject(Stub $stub, $isNested)
    {
        $obj = $stub->value;
        $class = $stub->class;

        if (isset($class[15]) && "\0" === $class[15] && 0 === strpos($class, "class@anonymous\x00")) {
            $stub->class = get_parent_class($class).'@anonymous';
        }
        if (isset($this->classInfo[$class])) {
            list($i, $parents, $hasDebugInfo, $fileInfo) = $this->classInfo[$class];
        } else {
            $i = 2;
            $parents = [$class];
            $hasDebugInfo = method_exists($class, '__debugInfo');

            foreach (class_parents($class) as $p) {
                $parents[] = $p;
                ++$i;
            }
            foreach (class_implements($class) as $p) {
                $parents[] = $p;
                ++$i;
            }
            $parents[] = '*';

            $r = new \ReflectionClass($class);
            $fileInfo = $r->isInternal() || $r->isSubclassOf(Stub::class) ? [] : [
                'file' => $r->getFileName(),
                'line' => $r->getStartLine(),
            ];

            $this->classInfo[$class] = [$i, $parents, $hasDebugInfo, $fileInfo];
        }

        $stub->attr += $fileInfo;
        $a = Caster::castObject($obj, $class, $hasDebugInfo);

        try {
            while ($i--) {
                if (!empty($this->casters[$p = $parents[$i]])) {
                    foreach ($this->casters[$p] as $callback) {
                        $a = $callback($obj, $a, $stub, $isNested, $this->filter);
                    }
                }
            }
        } catch (\Exception $e) {
            $a = [(Stub::TYPE_OBJECT === $stub->type ? Caster::PREFIX_VIRTUAL : '').'⚠' => new ThrowingCasterException($e)] + $a;
        }

        return $a;
    }

    /**
     * Casts a resource to an array representation.
     *
     * @param Stub $stub     The Stub for the casted resource
     * @param bool $isNested True if the object is nested in the dumped structure
     *
     * @return array The resource casted as array
     */
    protected function castResource(Stub $stub, $isNested)
    {
        $a = [];
        $res = $stub->value;
        $type = $stub->class;

        try {
            if (!empty($this->casters[':'.$type])) {
                foreach ($this->casters[':'.$type] as $callback) {
                    $a = $callback($res, $a, $stub, $isNested, $this->filter);
                }
            }
        } catch (\Exception $e) {
            $a = [(Stub::TYPE_OBJECT === $stub->type ? Caster::PREFIX_VIRTUAL : '').'⚠' => new ThrowingCasterException($e)] + $a;
        }

        return $a;
    }
}
