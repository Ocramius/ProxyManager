<?php

declare(strict_types=1);

namespace ProxyManagerTest\ProxyGenerator\LazyLoadingValueHolder\MethodGenerator;

use Laminas\Code\Generator\ParameterGenerator;
use Laminas\Code\Generator\PropertyGenerator;
use Laminas\Code\Generator\TypeGenerator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ProxyManager\ProxyGenerator\LazyLoadingValueHolder\MethodGenerator\SetProxyInitializer;
use function array_shift;

/**
 * Tests for {@see \ProxyManager\ProxyGenerator\LazyLoadingValueHolder\MethodGenerator\SetProxyInitializer}
 *
 * @group Coverage
 */
final class SetProxyInitializerTest extends TestCase
{
    /**
     * @covers \ProxyManager\ProxyGenerator\LazyLoadingValueHolder\MethodGenerator\SetProxyInitializer::__construct
     */
    public function testBodyStructure() : void
    {
        $initializer = $this->createMock(PropertyGenerator::class);

        $initializer->method('getName')->willReturn('foo');

        $setter     = new SetProxyInitializer($initializer);
        $parameters = $setter->getParameters();

        self::assertSame('setProxyInitializer', $setter->getName());
        self::assertCount(1, $parameters);

        $initializer = array_shift($parameters);

        self::assertInstanceOf(ParameterGenerator::class, $initializer);
        self::assertSame('initializer', $initializer->getName());
        self::assertSame('$this->foo = $initializer;', $setter->getBody());
        self::assertEquals(TypeGenerator::fromTypeString('void'), $setter->getReturnType());
    }
}
