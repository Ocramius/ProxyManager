<?php

declare(strict_types=1);

namespace ProxyManagerTest\ProxyGenerator\Util;

use ArrayAccess;
use InvalidArgumentException;
use Laminas\Code\Generator\PropertyGenerator;
use PHPUnit\Framework\TestCase;
use ProxyManager\ProxyGenerator\Util\PublicScopeSimulator;
use ReflectionClass;

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
            $realInstanceReflection->getName(),
            $foo,
            $backtrace[0]['file'],
            $backtrace[0]['line']
        ),
        \E_USER_NOTICE
    );
    return $targetObject->$foo;
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

    public function testValueParameterNameIgnoredForSetOperation(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Parameter $valueParameter should be provided (only) when $operationType === "set"');

        PublicScopeSimulator::getPublicAccessSimulationCode(
            PublicScopeSimulator::OPERATION_GET,
            'foo',
            'givenValue',
            null,
            'bar'
        );
    }

    public function testSimpleSet(): void
    {
        $expected = <<<'PHP'
$realInstanceReflection = new \ReflectionClass(get_parent_class($this));

if (! $realInstanceReflection->hasProperty($foo)) {
    $targetObject = $this;

    $targetObject->$foo = $baz;

    return $targetObject->$foo;
}

$targetObject = $realInstanceReflection->newInstanceWithoutConstructor();
$accessor = function & () use ($targetObject, $foo, $baz) {
    $targetObject->$foo = $baz;

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

    return;
};
$backtrace = debug_backtrace(true, 2);
$scopeObject = isset($backtrace[1]['object']) ? $backtrace[1]['object'] : new \ProxyManager\Stub\EmptyClassStub();
$accessor = $accessor->bindTo($scopeObject, get_class($scopeObject));
$accessor();
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

    /**
     * @group #632
     * @group #645
     * @group #646
     */
    public function testUnsetCodeWillNotProduceReturnValueStatements(): void
    {
        self::assertStringNotContainsString(
            'return ',
            PublicScopeSimulator::getPublicAccessSimulationCode(
                PublicScopeSimulator::OPERATION_UNSET,
                'foo'
            ),
            'Generated return statements do not contain value expressions (invalid since PHP 7.3+)'
        );
    }

    /**
     * @group #632
     * @group #645
     * @group #646
     */
    public function testUnsetCodeWillNotAssignAccessorEvaluationToVariable(): void
    {
        self::assertStringEndsWith(
            "\n" . '$accessor();',
            PublicScopeSimulator::getPublicAccessSimulationCode(
                PublicScopeSimulator::OPERATION_UNSET,
                'foo'
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

    $targetObject->$foo = $baz;

    return $targetObject->$foo;
}

$targetObject = $this->valueHolder;
$accessor = function & () use ($targetObject, $foo, $baz) {
    $targetObject->$foo = $baz;

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
            $realInstanceReflection->getName(),
            $foo,
            $backtrace[0]['file'],
            $backtrace[0]['line']
        ),
        \E_USER_NOTICE
    );
    return $targetObject->$foo;
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

    /** @group #642 */
    public function testWillNotAttemptToGetParentClassWhenReflectionClassIsGivenUpfront(): void
    {
        self::assertStringStartsWith(
            <<<'PHP'
$realInstanceReflection = new \ReflectionClass('ArrayAccess');
PHP
            ,
            PublicScopeSimulator::getPublicAccessSimulationCode(
                PublicScopeSimulator::OPERATION_GET,
                'foo',
                null,
                null,
                null,
                new ReflectionClass(ArrayAccess::class)
            )
        );
    }
}
