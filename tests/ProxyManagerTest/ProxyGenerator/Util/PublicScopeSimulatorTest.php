<?php

declare(strict_types=1);

namespace ProxyManagerTest\ProxyGenerator\Util;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use ProxyManager\ProxyGenerator\Util\PublicScopeSimulator;
use Zend\Code\Generator\PropertyGenerator;

/**
 * Tests for {@see \ProxyManager\ProxyGenerator\Util\PublicScopeSimulator}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 *
 * @covers \ProxyManager\ProxyGenerator\Util\PublicScopeSimulator
 * @group Coverage
 */
class PublicScopeSimulatorTest extends TestCase
{
    public function testSimpleGet() : void
    {
        $expected = <<<'PHP'
$realInstanceReflection = new \ReflectionClass(get_parent_class($this));

if (! $realInstanceReflection->hasProperty($foo)) {
    $targetObject = $this;

    $backtrace = debug_backtrace(false);
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

$targetObject = unserialize(sprintf('O:%d:"%s":0:{}', strlen(get_parent_class($this)), get_parent_class($this)));
$accessor = function & () use ($targetObject, $name) {
    return $targetObject->$foo;
};
$backtrace = debug_backtrace(true);
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

    public function testSimpleSet() : void
    {
        $expected = <<<'PHP'
$realInstanceReflection = new \ReflectionClass(get_parent_class($this));

if (! $realInstanceReflection->hasProperty($foo)) {
    $targetObject = $this;

    return $targetObject->$foo = $baz;
    return;
}

$targetObject = unserialize(sprintf('O:%d:"%s":0:{}', strlen(get_parent_class($this)), get_parent_class($this)));
$accessor = function & () use ($targetObject, $name, $value) {
    return $targetObject->$foo = $baz;
};
$backtrace = debug_backtrace(true);
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

    public function testSimpleIsset() : void
    {
        $expected = <<<'PHP'
$realInstanceReflection = new \ReflectionClass(get_parent_class($this));

if (! $realInstanceReflection->hasProperty($foo)) {
    $targetObject = $this;

    return isset($targetObject->$foo);
    return;
}

$targetObject = unserialize(sprintf('O:%d:"%s":0:{}', strlen(get_parent_class($this)), get_parent_class($this)));
$accessor = function () use ($targetObject, $name) {
    return isset($targetObject->$foo);
};
$backtrace = debug_backtrace(true);
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

    public function testSimpleUnset() : void
    {
        $expected = <<<'PHP'
$realInstanceReflection = new \ReflectionClass(get_parent_class($this));

if (! $realInstanceReflection->hasProperty($foo)) {
    $targetObject = $this;

    unset($targetObject->$foo);
    return;
}

$targetObject = unserialize(sprintf('O:%d:"%s":0:{}', strlen(get_parent_class($this)), get_parent_class($this)));
$accessor = function () use ($targetObject, $name) {
    unset($targetObject->$foo);
};
$backtrace = debug_backtrace(true);
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

    public function testSetRequiresValueParameterName() : void
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

    public function testDelegatesToValueHolderWhenAvailable() : void
    {
        $expected = <<<'PHP'
$realInstanceReflection = new \ReflectionClass(get_parent_class($this));

if (! $realInstanceReflection->hasProperty($foo)) {
    $targetObject = $this->valueHolder;

    return $targetObject->$foo = $baz;
    return;
}

$targetObject = $this->valueHolder;
$accessor = function & () use ($targetObject, $name, $value) {
    return $targetObject->$foo = $baz;
};
$backtrace = debug_backtrace(true);
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

    public function testSetRequiresValidOperation() : void
    {
        $this->expectException(InvalidArgumentException::class);

        PublicScopeSimulator::getPublicAccessSimulationCode('invalid', 'foo');
    }

    public function testWillReturnDirectlyWithNoReturnParam() : void
    {
        $expected = <<<'PHP'
$realInstanceReflection = new \ReflectionClass(get_parent_class($this));

if (! $realInstanceReflection->hasProperty($foo)) {
    $targetObject = $this;

    $backtrace = debug_backtrace(false);
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

$targetObject = unserialize(sprintf('O:%d:"%s":0:{}', strlen(get_parent_class($this)), get_parent_class($this)));
$accessor = function & () use ($targetObject, $name) {
    return $targetObject->$foo;
};
$backtrace = debug_backtrace(true);
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
}
