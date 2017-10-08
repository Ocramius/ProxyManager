<?php

declare(strict_types=1);

namespace ProxyManagerTest\ProxyGenerator\LazyLoadingValueHolder\MethodGenerator;

use PHPUnit\Framework\TestCase;
use ProxyManager\ProxyGenerator\LazyLoadingValueHolder\MethodGenerator\GetProxyInitializer;
use Zend\Code\Generator\PropertyGenerator;

/**
 * Tests for {@see \ProxyManager\ProxyGenerator\LazyLoadingValueHolder\MethodGenerator\GetProxyInitializer}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
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
        /* @var $initializer PropertyGenerator|\PHPUnit_Framework_MockObject_MockObject */
        $initializer = $this->createMock(PropertyGenerator::class);

        $initializer->expects(self::any())->method('getName')->will(self::returnValue('foo'));

        $getter = new GetProxyInitializer($initializer);

        self::assertSame('getProxyInitializer', $getter->getName());
        self::assertCount(0, $getter->getParameters());
        self::assertSame('return $this->foo;', $getter->getBody());
    }
}
