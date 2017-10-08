<?php

declare(strict_types=1);

namespace ProxyManagerTest\ProxyGenerator\LazyLoadingValueHolder\MethodGenerator;

use PHPUnit\Framework\TestCase;
use Zend\Code\Generator\ParameterGenerator;
use ProxyManager\ProxyGenerator\LazyLoadingValueHolder\MethodGenerator\SetProxyInitializer;
use Zend\Code\Generator\PropertyGenerator;

/**
 * Tests for {@see \ProxyManager\ProxyGenerator\LazyLoadingValueHolder\MethodGenerator\SetProxyInitializer}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
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
        /* @var $initializer PropertyGenerator|\PHPUnit_Framework_MockObject_MockObject */
        $initializer = $this->createMock(PropertyGenerator::class);

        $initializer->expects(self::any())->method('getName')->will(self::returnValue('foo'));

        $setter     = new SetProxyInitializer($initializer);
        $parameters = $setter->getParameters();

        self::assertSame('setProxyInitializer', $setter->getName());
        self::assertCount(1, $parameters);

        /* @var $initializer ParameterGenerator */
        $initializer = array_shift($parameters);

        self::assertInstanceOf(ParameterGenerator::class, $initializer);
        self::assertSame('initializer', $initializer->getName());
        self::assertSame('$this->foo = $initializer;', $setter->getBody());
    }
}
