<?php

declare(strict_types=1);

namespace ProxyManagerTest\ProxyGenerator\LazyLoadingGhost\MethodGenerator;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ProxyManager\ProxyGenerator\LazyLoadingGhost\MethodGenerator\GetProxyInitializer;
use Zend\Code\Generator\PropertyGenerator;
use Zend\Code\Generator\TypeGenerator;

/**
 * Tests for {@see \ProxyManager\ProxyGenerator\LazyLoadingGhost\MethodGenerator\GetProxyInitializer}
 *
 * @group Coverage
 */
final class GetProxyInitializerTest extends TestCase
{
    /**
     * @covers \ProxyManager\ProxyGenerator\LazyLoadingGhost\MethodGenerator\GetProxyInitializer::__construct
     */
    public function testBodyStructure() : void
    {
        /** @var PropertyGenerator&MockObject $initializer */
        $initializer = $this->createMock(PropertyGenerator::class);

        $initializer->method('getName')->willReturn('foo');

        $getter = new GetProxyInitializer($initializer);

        self::assertEquals(TypeGenerator::fromTypeString('?\Closure'), $getter->getReturnType());
        self::assertSame('getProxyInitializer', $getter->getName());
        self::assertCount(0, $getter->getParameters());
        self::assertSame('return $this->foo;', $getter->getBody());
    }
}
