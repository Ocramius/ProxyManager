<?php

declare(strict_types=1);

namespace ProxyManagerTest\ProxyGenerator\AccessInterceptorValueHolder\MethodGenerator;

use PHPUnit\Framework\TestCase;
use ProxyManager\ProxyGenerator\AccessInterceptorValueHolder\MethodGenerator\StaticProxyConstructor;
use ProxyManagerTestAsset\ClassWithMixedProperties;
use ProxyManagerTestAsset\EmptyClass;
use ProxyManagerTestAsset\ProxyGenerator\LazyLoading\MethodGenerator\ClassWithTwoPublicProperties;
use ReflectionClass;
use Zend\Code\Generator\PropertyGenerator;

/**
 * Tests for {@see \ProxyManager\ProxyGenerator\AccessInterceptorValueHolder\MethodGenerator\StaticProxyConstructor}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 *
 * @covers \ProxyManager\ProxyGenerator\AccessInterceptorValueHolder\MethodGenerator\StaticProxyConstructor
 * @group Coverage
 */
class StaticProxyConstructorTest extends TestCase
{
    public function testBodyStructure() : void
    {
        /* @var $valueHolder PropertyGenerator|\PHPUnit_Framework_MockObject_MockObject */
        $valueHolder        = $this->createMock(PropertyGenerator::class);
        /* @var $prefixInterceptors PropertyGenerator|\PHPUnit_Framework_MockObject_MockObject */
        $prefixInterceptors = $this->createMock(PropertyGenerator::class);
        /* @var $suffixInterceptors PropertyGenerator|\PHPUnit_Framework_MockObject_MockObject */
        $suffixInterceptors = $this->createMock(PropertyGenerator::class);

        $valueHolder->expects(self::any())->method('getName')->will(self::returnValue('foo'));
        $prefixInterceptors->expects(self::any())->method('getName')->will(self::returnValue('pre'));
        $suffixInterceptors->expects(self::any())->method('getName')->will(self::returnValue('post'));

        $constructor = new StaticProxyConstructor(
            new ReflectionClass(
                ClassWithTwoPublicProperties::class
            ),
            $valueHolder,
            $prefixInterceptors,
            $suffixInterceptors
        );

        self::assertSame('staticProxyConstructor', $constructor->getName());
        self::assertTrue($constructor->isStatic());
        self::assertSame('public', $constructor->getVisibility());
        self::assertCount(3, $constructor->getParameters());
        self::assertSame(
            'static $reflection;

$reflection = $reflection ?: $reflection = new \ReflectionClass(__CLASS__);
$instance = (new \ReflectionClass(get_class()))->newInstanceWithoutConstructor();

unset($instance->bar, $instance->baz);

$instance->foo = $wrappedObject;
$instance->pre = $prefixInterceptors;
$instance->post = $suffixInterceptors;

return $instance;',
            $constructor->getBody()
        );
    }

    public function testBodyStructureWithoutPublicProperties() : void
    {
        /* @var $valueHolder PropertyGenerator|\PHPUnit_Framework_MockObject_MockObject */
        $valueHolder        = $this->createMock(PropertyGenerator::class);
        /* @var $prefixInterceptors PropertyGenerator|\PHPUnit_Framework_MockObject_MockObject */
        $prefixInterceptors = $this->createMock(PropertyGenerator::class);
        /* @var $suffixInterceptors PropertyGenerator|\PHPUnit_Framework_MockObject_MockObject */
        $suffixInterceptors = $this->createMock(PropertyGenerator::class);

        $valueHolder->expects(self::any())->method('getName')->will(self::returnValue('foo'));
        $prefixInterceptors->expects(self::any())->method('getName')->will(self::returnValue('pre'));
        $suffixInterceptors->expects(self::any())->method('getName')->will(self::returnValue('post'));

        $constructor = new StaticProxyConstructor(
            new ReflectionClass(EmptyClass::class),
            $valueHolder,
            $prefixInterceptors,
            $suffixInterceptors
        );

        self::assertSame(
            'static $reflection;

$reflection = $reflection ?: $reflection = new \ReflectionClass(__CLASS__);
$instance = (new \ReflectionClass(get_class()))->newInstanceWithoutConstructor();

$instance->foo = $wrappedObject;
$instance->pre = $prefixInterceptors;
$instance->post = $suffixInterceptors;

return $instance;',
            $constructor->getBody()
        );
    }

    /**
     * @group 276
     */
    public function testUnsetsPrivatePropertiesAsWell() : void
    {
        /* @var $valueHolder PropertyGenerator|\PHPUnit_Framework_MockObject_MockObject */
        $valueHolder        = $this->createMock(PropertyGenerator::class);
        /* @var $prefixInterceptors PropertyGenerator|\PHPUnit_Framework_MockObject_MockObject */
        $prefixInterceptors = $this->createMock(PropertyGenerator::class);
        /* @var $suffixInterceptors PropertyGenerator|\PHPUnit_Framework_MockObject_MockObject */
        $suffixInterceptors = $this->createMock(PropertyGenerator::class);

        $valueHolder->expects(self::any())->method('getName')->will(self::returnValue('foo'));
        $prefixInterceptors->expects(self::any())->method('getName')->will(self::returnValue('pre'));
        $suffixInterceptors->expects(self::any())->method('getName')->will(self::returnValue('post'));

        $constructor = new StaticProxyConstructor(
            new ReflectionClass(ClassWithMixedProperties::class),
            $valueHolder,
            $prefixInterceptors,
            $suffixInterceptors
        );

        self::assertContains(
            'unset($instance->publicProperty0, $instance->publicProperty1, $instance->publicProperty2, '
            . '$instance->protectedProperty0, $instance->protectedProperty1, $instance->protectedProperty2);

\Closure::bind(function (\ProxyManagerTestAsset\ClassWithMixedProperties $instance) {
    unset($instance->privateProperty0, $instance->privateProperty1, $instance->privateProperty2);
}, $instance, \'ProxyManagerTestAsset\\\\ClassWithMixedProperties\')->__invoke($instance);',
            $constructor->getBody()
        );
    }
}
