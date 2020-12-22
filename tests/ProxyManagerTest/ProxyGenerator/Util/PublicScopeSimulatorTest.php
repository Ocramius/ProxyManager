<?php

declare(strict_types=1);

namespace ProxyManagerTest\ProxyGenerator\Util;

use InvalidArgumentException;
use Laminas\Code\Generator\PropertyGenerator;
use PHPUnit\Framework\TestCase;
use ProxyManager\ProxyGenerator\Util\PublicScopeSimulator;
use ProxyManagerTestAsset\ClassWithMixedProperties;

use function sprintf;

/**
 * Tests for {@see \ProxyManager\ProxyGenerator\Util\PublicScopeSimulator}
 *
 * @covers \ProxyManager\ProxyGenerator\Util\PublicScopeSimulator
 * @group Coverage
 */
final class PublicScopeSimulatorTest extends TestCase
{
    public function testSimpleGet(): void
    {
        $expected = <<<'PHP'
$realInstanceReflection = new \ReflectionClass(get_parent_class($this));

if (! $realInstanceReflection->hasProperty($foo)) {
    $targetObject = $this;

    $backtrace = debug_backtrace(false, 1);
    trigger_error(
        sprintf(
            'Undefined property: %s::$%s in %s on line %s',
            get_parent_class($this),
            $foo,
            $backtrace[0]['file'],
            $backtrace[0]['line']
        ),
        \E_USER_NOTICE
    );
    return $targetObject->$foo;
    return;
}

$targetObject = $realInstanceReflection->newInstanceWithoutConstructor();
$accessor = function & () use ($targetObject, $foo) {
    return $targetObject->$foo;
};
$backtrace = debug_backtrace(true, 2);
$scopeObject = isset($backtrace[1]['object']) ? $backtrace[1]['object'] : new \ProxyManager\Stub\EmptyClassStub();
$accessor = $accessor->bindTo($scopeObject, get_class($scopeObject));
$bar = & $accessor();
PHP;

        self::assertSame(
            $expected,
            PublicScopeSimulator::getPublicAccessSimulationCode(
                PublicScopeSimulator::OPERATION_GET,
                'foo',
                null,
                null,
                'bar'
            )
        );
    }

    public function testSimpleSet(): void
    {
        $expected = <<<'PHP'
$realInstanceReflection = new \ReflectionClass(get_parent_class($this));

if (! $realInstanceReflection->hasProperty($foo)) {
    $targetObject = $this;

    $targetObject->$foo = $baz; return $targetObject->$foo;
    return;
}

$targetObject = $realInstanceReflection->newInstanceWithoutConstructor();
$accessor = function & () use ($targetObject, $foo, $baz) {
    $targetObject->$foo = $baz; return $targetObject->$foo;
};
$backtrace = debug_backtrace(true, 2);
$scopeObject = isset($backtrace[1]['object']) ? $backtrace[1]['object'] : new \ProxyManager\Stub\EmptyClassStub();
$accessor = $accessor->bindTo($scopeObject, get_class($scopeObject));
$bar = & $accessor();
PHP;

        self::assertSame(
            $expected,
            PublicScopeSimulator::getPublicAccessSimulationCode(
                PublicScopeSimulator::OPERATION_SET,
                'foo',
                'baz',
                null,
                'bar'
            )
        );
    }

    public function testSimpleIsset(): void
    {
        $expected = <<<'PHP'
$realInstanceReflection = new \ReflectionClass(get_parent_class($this));

if (! $realInstanceReflection->hasProperty($foo)) {
    $targetObject = $this;

    return isset($targetObject->$foo);
    return;
}

$targetObject = $realInstanceReflection->newInstanceWithoutConstructor();
$accessor = function () use ($targetObject, $foo) {
    return isset($targetObject->$foo);
};
$backtrace = debug_backtrace(true, 2);
$scopeObject = isset($backtrace[1]['object']) ? $backtrace[1]['object'] : new \ProxyManager\Stub\EmptyClassStub();
$accessor = $accessor->bindTo($scopeObject, get_class($scopeObject));
$bar = $accessor();
PHP;

        self::assertSame(
            $expected,
            PublicScopeSimulator::getPublicAccessSimulationCode(
                PublicScopeSimulator::OPERATION_ISSET,
                'foo',
                null,
                null,
                'bar'
            )
        );
    }

    public function testSimpleUnset(): void
    {
        $expected = <<<'PHP'
$realInstanceReflection = new \ReflectionClass(get_parent_class($this));

if (! $realInstanceReflection->hasProperty($foo)) {
    $targetObject = $this;

    unset($targetObject->$foo);
    return;
}

$targetObject = $realInstanceReflection->newInstanceWithoutConstructor();
$accessor = function () use ($targetObject, $foo) {
    unset($targetObject->$foo);
};
$backtrace = debug_backtrace(true, 2);
$scopeObject = isset($backtrace[1]['object']) ? $backtrace[1]['object'] : new \ProxyManager\Stub\EmptyClassStub();
$accessor = $accessor->bindTo($scopeObject, get_class($scopeObject));
$bar = $accessor();
PHP;

        self::assertSame(
            $expected,
            PublicScopeSimulator::getPublicAccessSimulationCode(
                PublicScopeSimulator::OPERATION_UNSET,
                'foo',
                null,
                null,
                'bar'
            )
        );
    }

    public function testSetRequiresValueParameterName(): void
    {
        $this->expectException(InvalidArgumentException::class);

        PublicScopeSimulator::getPublicAccessSimulationCode(
            PublicScopeSimulator::OPERATION_SET,
            'foo',
            null,
            null,
            'bar'
        );
    }

    public function testDelegatesToValueHolderWhenAvailable(): void
    {
        $expected = <<<'PHP'
$realInstanceReflection = new \ReflectionClass(get_parent_class($this));

if (! $realInstanceReflection->hasProperty($foo)) {
    $targetObject = $this->valueHolder;

    $targetObject->$foo = $baz; return $targetObject->$foo;
    return;
}

$targetObject = $this->valueHolder;
$accessor = function & () use ($targetObject, $foo, $baz) {
    $targetObject->$foo = $baz; return $targetObject->$foo;
};
$backtrace = debug_backtrace(true, 2);
$scopeObject = isset($backtrace[1]['object']) ? $backtrace[1]['object'] : new \ProxyManager\Stub\EmptyClassStub();
$accessor = $accessor->bindTo($scopeObject, get_class($scopeObject));
$bar = & $accessor();
PHP;

        self::assertSame(
            $expected,
            PublicScopeSimulator::getPublicAccessSimulationCode(
                PublicScopeSimulator::OPERATION_SET,
                'foo',
                'baz',
                new PropertyGenerator('valueHolder'),
                'bar'
            )
        );
    }

    public function testSetRequiresValidOperation(): void
    {
        $this->expectException(InvalidArgumentException::class);

        PublicScopeSimulator::getPublicAccessSimulationCode('invalid', 'foo');
    }

    public function testWillReturnDirectlyWithNoReturnParam(): void
    {
        $expected = <<<'PHP'
$realInstanceReflection = new \ReflectionClass(get_parent_class($this));

if (! $realInstanceReflection->hasProperty($foo)) {
    $targetObject = $this;

    $backtrace = debug_backtrace(false, 1);
    trigger_error(
        sprintf(
            'Undefined property: %s::$%s in %s on line %s',
            get_parent_class($this),
            $foo,
            $backtrace[0]['file'],
            $backtrace[0]['line']
        ),
        \E_USER_NOTICE
    );
    return $targetObject->$foo;
    return;
}

$targetObject = $realInstanceReflection->newInstanceWithoutConstructor();
$accessor = function & () use ($targetObject, $foo) {
    return $targetObject->$foo;
};
$backtrace = debug_backtrace(true, 2);
$scopeObject = isset($backtrace[1]['object']) ? $backtrace[1]['object'] : new \ProxyManager\Stub\EmptyClassStub();
$accessor = $accessor->bindTo($scopeObject, get_class($scopeObject));
$returnValue = & $accessor();

return $returnValue;
PHP;

        self::assertSame(
            $expected,
            PublicScopeSimulator::getPublicAccessSimulationCode(
                PublicScopeSimulator::OPERATION_GET,
                'foo'
            )
        );
    }

    public function testFunctional(): void
    {
        /** @psalm-var ClassWithMixedProperties $sut */
        $sut = eval(
            sprintf(
                'return new class() extends %s
                {
                    public function doGet($prop) { %s }
                    public function doSet($prop, $val) { %s }
                    public function doIsset($prop) { %s }
                    public function doUnset($prop) { %s }
                };',
                ClassWithMixedProperties::class,
                PublicScopeSimulator::getPublicAccessSimulationCode(PublicScopeSimulator::OPERATION_GET, 'prop'),
                PublicScopeSimulator::getPublicAccessSimulationCode(PublicScopeSimulator::OPERATION_SET, 'prop', 'val'),
                PublicScopeSimulator::getPublicAccessSimulationCode(PublicScopeSimulator::OPERATION_ISSET, 'prop'),
                PublicScopeSimulator::getPublicAccessSimulationCode(PublicScopeSimulator::OPERATION_UNSET, 'prop'),
            )
        );

        $this->assertSame('publicProperty0', $sut->doGet('publicProperty0'));
        $this->assertSame('bar', $sut->doSet('publicProperty0', 'bar'));
        $this->assertTrue($sut->doIsset('publicProperty0'));
        $this->assertNull($sut->doUnset('publicProperty0'));
    }
}
