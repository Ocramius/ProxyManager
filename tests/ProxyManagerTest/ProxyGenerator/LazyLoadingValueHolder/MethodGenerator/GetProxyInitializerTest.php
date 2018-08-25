<?php

declare(strict_types=1);

namespace ProxyManagerTest\ProxyGenerator\LazyLoadingValueHolder\MethodGenerator;

use PHPUnit\Framework\TestCase;
use ProxyManager\ProxyGenerator\LazyLoadingValueHolder\MethodGenerator\GetProxyInitializer;
use Zend\Code\Generator\PropertyGenerator;
use Zend\Code\Generator\TypeGenerator;

/**
 * Tests for {@see \ProxyManager\ProxyGenerator\LazyLoadingValueHolder\MethodGenerator\GetProxyInitializer}
 *
 * @group Coverage
 */
class GetProxyInitializerTest extends TestCase
{
    /**
     * @covers \ProxyManager\ProxyGenerator\LazyLoadingValueHolder\MethodGenerator\GetProxyInitializer::__construct
     */
    public function testBodyStructure() : void
    {
        /** @var PropertyGenerator|\PHPUnit_Framework_MockObject_MockObject $initializer */
        $initializer = $this->createMock(PropertyGenerator::class);

        $initializer->expects(self::any())->method('getName')->will(self::returnValue('foo'));

        $getter = new GetProxyInitializer($initializer);

        self::assertEquals(TypeGenerator::fromTypeString('?\Closure'), $getter->getReturnType());
        self::assertSame('getProxyInitializer', $getter->getName());
        self::assertCount(0, $getter->getParameters());
        self::assertSame('return $this->foo;', $getter->getBody());
    }
}
