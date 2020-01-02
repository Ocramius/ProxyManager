<?php

declare(strict_types=1);

namespace ProxyManagerTest\ProxyGenerator\LazyLoadingValueHolder\MethodGenerator;

use Laminas\Code\Generator\PropertyGenerator;
use Laminas\Code\Reflection\MethodReflection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ProxyManager\ProxyGenerator\LazyLoadingValueHolder\MethodGenerator\LazyLoadingMethodInterceptor;
use ProxyManagerTestAsset\BaseClass;

/**
 * Tests for {@see \ProxyManager\ProxyGenerator\LazyLoadingValueHolder\MethodGenerator\LazyLoadingMethodInterceptor}
 *
 * @group Coverage
 */
final class LazyLoadingMethodInterceptorTest extends TestCase
{
    /**
     * @covers \ProxyManager\ProxyGenerator\LazyLoadingValueHolder\MethodGenerator\LazyLoadingMethodInterceptor
     */
    public function testBodyStructure() : void
    {
        $initializer = $this->createMock(PropertyGenerator::class);
        $valueHolder = $this->createMock(PropertyGenerator::class);

        $initializer->method('getName')->willReturn('foo');
        $valueHolder->method('getName')->willReturn('bar');

        $reflection = new MethodReflection(BaseClass::class, 'publicByReferenceParameterMethod');
        $method     = LazyLoadingMethodInterceptor::generateMethod($reflection, $initializer, $valueHolder);

        self::assertSame('publicByReferenceParameterMethod', $method->getName());
        self::assertCount(2, $method->getParameters());
        self::assertSame(
            "\$this->foo && \$this->foo->__invoke(\$this->bar, \$this, 'publicByReferenceParameterMethod', "
            . "array('param' => \$param, 'byRefParam' => \$byRefParam), \$this->foo);\n\n"
            . 'return $this->bar->publicByReferenceParameterMethod($param, $byRefParam);',
            $method->getBody()
        );
    }

    /**
     * @covers \ProxyManager\ProxyGenerator\LazyLoadingValueHolder\MethodGenerator\LazyLoadingMethodInterceptor
     */
    public function testBodyStructureWithoutParameters() : void
    {
        $reflectionMethod = new MethodReflection(BaseClass::class, 'publicMethod');
        $initializer      = $this->createMock(PropertyGenerator::class);
        $valueHolder      = $this->createMock(PropertyGenerator::class);

        $initializer->method('getName')->willReturn('foo');
        $valueHolder->method('getName')->willReturn('bar');

        $initializer->method('getName')->willReturn('foo');

        $method = LazyLoadingMethodInterceptor::generateMethod($reflectionMethod, $initializer, $valueHolder);

        self::assertSame('publicMethod', $method->getName());
        self::assertCount(0, $method->getParameters());
        self::assertSame(
            '$this->foo && $this->foo->__invoke($this->bar, $this, '
            . "'publicMethod', array(), \$this->foo);\n\n"
            . 'return $this->bar->publicMethod();',
            $method->getBody()
        );
    }
}
