<?php

declare(strict_types=1);

namespace ProxyManagerTest\ProxyGenerator\ValueHolder\MethodGenerator;

use PHPUnit_Framework_TestCase;
use ProxyManager\ProxyGenerator\ValueHolder\MethodGenerator\Constructor;
use ProxyManagerTestAsset\ClassWithMixedProperties;
use ProxyManagerTestAsset\ClassWithVariadicConstructorArgument;
use ProxyManagerTestAsset\EmptyClass;
use ProxyManagerTestAsset\ProxyGenerator\LazyLoading\MethodGenerator\ClassWithTwoPublicProperties;
use ReflectionClass;
use Zend\Code\Generator\PropertyGenerator;

/**
 * Tests for {@see \ProxyManager\ProxyGenerator\ValueHolder\MethodGenerator\Constructor}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 *
 * @covers \ProxyManager\ProxyGenerator\ValueHolder\MethodGenerator\Constructor
 * @group Coverage
 */
class ConstructorTest extends PHPUnit_Framework_TestCase
{
    public function testBodyStructure() : void
    {
        /* @var $valueHolder PropertyGenerator|\PHPUnit_Framework_MockObject_MockObject */
        $valueHolder = $this->createMock(PropertyGenerator::class);

        $valueHolder->expects(self::any())->method('getName')->will(self::returnValue('foo'));

        $constructor = Constructor::generateMethod(
            new ReflectionClass(
                ClassWithTwoPublicProperties::class
            ),
            $valueHolder
        );

        self::assertSame('__construct', $constructor->getName());
        self::assertCount(0, $constructor->getParameters());
        self::assertSame(
            'static $reflection;

if (! $this->foo) {
    $reflection = $reflection ?: new \ReflectionClass(\'ProxyManagerTestAsset\\\\ProxyGenerator\\\\LazyLoading\\\\'
            . 'MethodGenerator\\\\ClassWithTwoPublicProperties\');
    $this->foo = $reflection->newInstanceWithoutConstructor();
unset($this->bar, $this->baz);

}',
            $constructor->getBody()
        );
    }

    public function testBodyStructureWithoutPublicProperties() : void
    {
        /* @var $valueHolder PropertyGenerator|\PHPUnit_Framework_MockObject_MockObject */
        $valueHolder = $this->createMock(PropertyGenerator::class);

        $valueHolder->expects(self::any())->method('getName')->will(self::returnValue('foo'));

        $constructor = Constructor::generateMethod(
            new ReflectionClass(EmptyClass::class),
            $valueHolder
        );

        self::assertSame('__construct', $constructor->getName());
        self::assertCount(0, $constructor->getParameters());
        self::assertSame(
            'static $reflection;

if (! $this->foo) {
    $reflection = $reflection ?: new \ReflectionClass(\'ProxyManagerTestAsset\\\\EmptyClass\');
    $this->foo = $reflection->newInstanceWithoutConstructor();
}',
            $constructor->getBody()
        );
    }

    public function testBodyStructureWithStaticProperties() : void
    {
        /* @var $valueHolder PropertyGenerator|\PHPUnit_Framework_MockObject_MockObject */
        $valueHolder = $this->createMock(PropertyGenerator::class);

        $valueHolder->expects(self::any())->method('getName')->will(self::returnValue('foo'));

        $constructor = Constructor::generateMethod(new ReflectionClass(ClassWithMixedProperties::class), $valueHolder);

        self::assertSame('__construct', $constructor->getName());
        self::assertCount(0, $constructor->getParameters());

        $expectedCode = 'static $reflection;

if (! $this->foo) {
    $reflection = $reflection ?: new \ReflectionClass(\'ProxyManagerTestAsset\\\\ClassWithMixedProperties\');
    $this->foo = $reflection->newInstanceWithoutConstructor();
unset($this->publicProperty0, $this->publicProperty1, $this->publicProperty2, $this->protectedProperty0, '
            . '$this->protectedProperty1, $this->protectedProperty2);

\Closure::bind(function (\ProxyManagerTestAsset\ClassWithMixedProperties $instance) {
    unset($instance->privateProperty0, $instance->privateProperty1, $instance->privateProperty2);
}, $this, \'ProxyManagerTestAsset\\\\ClassWithMixedProperties\')->__invoke($this);

}';

        self::assertSame($expectedCode, $constructor->getBody());
    }

    public function testBodyStructureWithVariadicArguments() : void
    {
        /* @var $valueHolder PropertyGenerator|\PHPUnit_Framework_MockObject_MockObject */
        $valueHolder = $this->createMock(PropertyGenerator::class);

        $valueHolder->expects(self::any())->method('getName')->will(self::returnValue('foo'));

        $constructor = Constructor::generateMethod(
            new ReflectionClass(ClassWithVariadicConstructorArgument::class),
            $valueHolder
        );

        self::assertSame('__construct', $constructor->getName());
        self::assertCount(2, $constructor->getParameters());

        $expectedCode = <<<'PHP'
static $reflection;

if (! $this->foo) {
    $reflection = $reflection ?: new \ReflectionClass('ProxyManagerTestAsset\\ClassWithVariadicConstructorArgument');
    $this->foo = $reflection->newInstanceWithoutConstructor();
\Closure::bind(function (\ProxyManagerTestAsset\ClassWithVariadicConstructorArgument $instance) {
    unset($instance->foo, $instance->bar);
}, $this, 'ProxyManagerTestAsset\\ClassWithVariadicConstructorArgument')->__invoke($this);

}

$this->foo->__construct($foo, ...$bar);
PHP;

        self::assertSame($expectedCode, $constructor->getBody());
    }
}
