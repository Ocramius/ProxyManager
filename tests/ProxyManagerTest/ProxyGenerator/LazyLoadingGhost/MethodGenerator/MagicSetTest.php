<?php

declare(strict_types=1);

namespace ProxyManagerTest\ProxyGenerator\LazyLoadingGhost\MethodGenerator;

use Laminas\Code\Generator\MethodGenerator;
use Laminas\Code\Generator\PropertyGenerator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ProxyManager\ProxyGenerator\LazyLoadingGhost\MethodGenerator\MagicSet;
use ProxyManager\ProxyGenerator\LazyLoadingGhost\PropertyGenerator\PrivatePropertiesMap;
use ProxyManager\ProxyGenerator\LazyLoadingGhost\PropertyGenerator\ProtectedPropertiesMap;
use ProxyManager\ProxyGenerator\PropertyGenerator\PublicPropertiesMap;
use ProxyManagerTestAsset\ClassWithMagicMethods;
use ProxyManagerTestAsset\EmptyClass;
use ReflectionClass;

/**
 * Tests for {@see \ProxyManager\ProxyGenerator\LazyLoadingGhost\MethodGenerator\MagicSet}
 *
 * @group Coverage
 */
final class MagicSetTest extends TestCase
{
    /** @var PropertyGenerator&MockObject */
    private PropertyGenerator $initializer;

    /** @var MethodGenerator&MockObject */
    private MethodGenerator $initMethod;

    /** @var PublicPropertiesMap&MockObject */
    private PublicPropertiesMap $publicProperties;

    /** @var ProtectedPropertiesMap&MockObject */
    private ProtectedPropertiesMap $protectedProperties;

    /** @var PrivatePropertiesMap&MockObject */
    private PrivatePropertiesMap $privateProperties;

    private string $expectedCode = <<<'PHP'
$this->foo && $this->baz('__set', array('name' => $name, 'value' => $value));

if (isset(self::$bar[$name])) {
    return ($this->$name = $value);
}

if (isset(self::$baz[$name])) {
    // check protected property access via compatible class
    $callers      = debug_backtrace(\DEBUG_BACKTRACE_PROVIDE_OBJECT, 2);
    $caller       = isset($callers[1]) ? $callers[1] : [];
    $object       = isset($caller['object']) ? $caller['object'] : '';
    $expectedType = self::$baz[$name];

    if ($object instanceof $expectedType) {
        return ($this->$name = $value);
    }

    $class = isset($caller['class']) ? $caller['class'] : '';

    if ($class === $expectedType || is_subclass_of($class, $expectedType) || $class === 'ReflectionProperty') {
        return ($this->$name = $value);
    }
} elseif (isset(self::$tab[$name])) {
    // check private property access via same class
    $callers = debug_backtrace(\DEBUG_BACKTRACE_PROVIDE_OBJECT, 2);
    $caller  = isset($callers[1]) ? $callers[1] : [];
    $class   = isset($caller['class']) ? $caller['class'] : '';

    static $accessorCache = [];

    if (isset(self::$tab[$name][$class])) {
        $cacheKey = $class . '#' . $name;
        $accessor = isset($accessorCache[$cacheKey])
            ? $accessorCache[$cacheKey]
            : $accessorCache[$cacheKey] = \Closure::bind(static function ($instance, $value) use ($name) {
                return ($instance->$name = $value);
            }, null, $class);

        return $accessor($this, $value);
    }

    if ('ReflectionProperty' === $class) {
        $tmpClass = key(self::$tab[$name]);
        $cacheKey = $tmpClass . '#' . $name;
        $accessor = isset($accessorCache[$cacheKey])
            ? $accessorCache[$cacheKey]
            : $accessorCache[$cacheKey] = \Closure::bind(static function ($instance, $value) use ($name) {
                return ($instance->$name = $value);
            }, null, $tmpClass);

        return $accessor($this, $value);
    }
}

%a
PHP;

    /**
     * {@inheritDoc}
     */
    protected function setUp() : void
    {
        $this->initializer         = $this->createMock(PropertyGenerator::class);
        $this->initMethod          = $this->createMock(MethodGenerator::class);
        $this->publicProperties    = $this->createMock(PublicPropertiesMap::class);
        $this->protectedProperties = $this->createMock(ProtectedPropertiesMap::class);
        $this->privateProperties   = $this->createMock(PrivatePropertiesMap::class);

        $this->initializer->method('getName')->willReturn('foo');
        $this->initMethod->method('getName')->willReturn('baz');
        $this->publicProperties->method('isEmpty')->willReturn(false);
        $this->publicProperties->method('getName')->willReturn('bar');
        $this->protectedProperties->method('getName')->willReturn('baz');
        $this->privateProperties->method('getName')->willReturn('tab');
    }

    /**
     * @covers \ProxyManager\ProxyGenerator\LazyLoadingGhost\MethodGenerator\MagicSet::__construct
     */
    public function testBodyStructure() : void
    {
        $magicSet = new MagicSet(
            new ReflectionClass(EmptyClass::class),
            $this->initializer,
            $this->initMethod,
            $this->publicProperties,
            $this->protectedProperties,
            $this->privateProperties
        );

        self::assertSame('__set', $magicSet->getName());
        self::assertCount(2, $magicSet->getParameters());
        self::assertStringMatchesFormat($this->expectedCode, $magicSet->getBody());
    }

    /**
     * @covers \ProxyManager\ProxyGenerator\LazyLoadingGhost\MethodGenerator\MagicSet::__construct
     */
    public function testBodyStructureWithOverriddenMagicGet() : void
    {
        $magicSet = new MagicSet(
            new ReflectionClass(ClassWithMagicMethods::class),
            $this->initializer,
            $this->initMethod,
            $this->publicProperties,
            $this->protectedProperties,
            $this->privateProperties
        );

        self::assertSame('__set', $magicSet->getName());
        self::assertCount(2, $magicSet->getParameters());

        $body = $magicSet->getBody();

        self::assertStringMatchesFormat($this->expectedCode, $body);
        self::assertStringMatchesFormat('%Areturn parent::__set($name, $value);', $body);
    }
}
