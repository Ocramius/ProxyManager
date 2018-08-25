<?php

declare(strict_types=1);

namespace ProxyManagerTest\ProxyGenerator\LazyLoadingValueHolder\MethodGenerator;

use PHPUnit\Framework\TestCase;
use ProxyManager\ProxyGenerator\LazyLoadingValueHolder\MethodGenerator\SetProxyInitializer;
use Zend\Code\Generator\ParameterGenerator;
use Zend\Code\Generator\PropertyGenerator;
use function array_shift;

/**
 * Tests for {@see \ProxyManager\ProxyGenerator\LazyLoadingValueHolder\MethodGenerator\SetProxyInitializer}
 *
 * @group Coverage
 */
class SetProxyInitializerTest extends TestCase
{
    /**
     * @covers \ProxyManager\ProxyGenerator\LazyLoadingValueHolder\MethodGenerator\SetProxyInitializer::__construct
     */
    public function testBodyStructure() : void
    {
        /** @var PropertyGenerator|\PHPUnit_Framework_MockObject_MockObject $initializer */
        $initializer = $this->createMock(PropertyGenerator::class);

        $initializer->expects(self::any())->method('getName')->will(self::returnValue('foo'));

        $setter     = new SetProxyInitializer($initializer);
        $parameters = $setter->getParameters();

        self::assertSame('setProxyInitializer', $setter->getName());
        self::assertCount(1, $parameters);

        /** @var ParameterGenerator $initializer */
        $initializer = array_shift($parameters);

        self::assertInstanceOf(ParameterGenerator::class, $initializer);
        self::assertSame('initializer', $initializer->getName());
        self::assertSame('$this->foo = $initializer;', $setter->getBody());
    }
}
