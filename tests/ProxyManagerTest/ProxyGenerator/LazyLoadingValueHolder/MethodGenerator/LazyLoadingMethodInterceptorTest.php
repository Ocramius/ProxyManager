<?php

declare(strict_types=1);

namespace ProxyManagerTest\ProxyGenerator\LazyLoadingValueHolder\MethodGenerator;

use PHPUnit_Framework_TestCase;
use ProxyManager\ProxyGenerator\LazyLoadingValueHolder\MethodGenerator\LazyLoadingMethodInterceptor;
use ProxyManagerTestAsset\BaseClass;
use Zend\Code\Generator\PropertyGenerator;
use Zend\Code\Reflection\MethodReflection;

/**
 * Tests for {@see \ProxyManager\ProxyGenerator\LazyLoadingValueHolder\MethodGenerator\LazyLoadingMethodInterceptor}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 *
 * @group Coverage
 */
class LazyLoadingMethodInterceptorTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers \ProxyManager\ProxyGenerator\LazyLoadingValueHolder\MethodGenerator\LazyLoadingMethodInterceptor
     */
    public function testBodyStructure() : void
    {
        /* @var $initializer PropertyGenerator|\PHPUnit_Framework_MockObject_MockObject */
        $initializer      = $this->createMock(PropertyGenerator::class);
        /* @var $valueHolder PropertyGenerator|\PHPUnit_Framework_MockObject_MockObject */
        $valueHolder      = $this->createMock(PropertyGenerator::class);

        $initializer->expects(self::any())->method('getName')->will(self::returnValue('foo'));
        $valueHolder->expects(self::any())->method('getName')->will(self::returnValue('bar'));

        $reflection = new MethodReflection(BaseClass::class, 'publicByReferenceParameterMethod');
        $method     = LazyLoadingMethodInterceptor::generateMethod($reflection, $initializer, $valueHolder);

        self::assertSame('publicByReferenceParameterMethod', $method->getName());
        self::assertCount(2, $method->getParameters());
        self::assertSame(
            "\$this->foo && \$this->foo->__invoke(\$this->bar, \$this, 'publicByReferenceParameterMethod', "
            . "array('param' => \$param, 'byRefParam' => \$byRefParam), \$this->foo);\n\n"
            . "return \$this->bar->publicByReferenceParameterMethod(\$param, \$byRefParam);",
            $method->getBody()
        );
    }

    /**
     * @covers \ProxyManager\ProxyGenerator\LazyLoadingValueHolder\MethodGenerator\LazyLoadingMethodInterceptor
     */
    public function testBodyStructureWithoutParameters() : void
    {
        $reflectionMethod = new MethodReflection(BaseClass::class, 'publicMethod');
        /* @var $initializer PropertyGenerator|\PHPUnit_Framework_MockObject_MockObject */
        $initializer      = $this->createMock(PropertyGenerator::class);
        /* @var $valueHolder PropertyGenerator|\PHPUnit_Framework_MockObject_MockObject */
        $valueHolder      = $this->createMock(PropertyGenerator::class);

        $initializer->expects(self::any())->method('getName')->will(self::returnValue('foo'));
        $valueHolder->expects(self::any())->method('getName')->will(self::returnValue('bar'));

        $initializer->expects(self::any())->method('getName')->will(self::returnValue('foo'));

        $method = LazyLoadingMethodInterceptor::generateMethod($reflectionMethod, $initializer, $valueHolder);

        self::assertSame('publicMethod', $method->getName());
        self::assertCount(0, $method->getParameters());
        self::assertSame(
            "\$this->foo && \$this->foo->__invoke(\$this->bar, \$this, "
            . "'publicMethod', array(), \$this->foo);\n\n"
            . "return \$this->bar->publicMethod();",
            $method->getBody()
        );
    }
}
