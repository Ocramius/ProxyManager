<?php

declare(strict_types=1);

namespace ProxyManagerTest\ProxyGenerator\RemoteObject\MethodGenerator;

use PHPUnit\Framework\TestCase;
use ProxyManager\ProxyGenerator\RemoteObject\MethodGenerator\RemoteObjectMethod;
use ProxyManagerTestAsset\BaseClass;
use ReflectionClass;
use Zend\Code\Generator\PropertyGenerator;
use Zend\Code\Reflection\MethodReflection;

/**
 * Tests for {@see \ProxyManager\ProxyGenerator\RemoteObject\MethodGenerator\RemoteObjectMethod}
 *
 * @author Vincent Blanchon <blanchon.vincent@gmail.com>
 * @license MIT
 *
 * @group Coverage
 */
class RemoteObjectMethodTest extends TestCase
{
    /**
     * @covers \ProxyManager\ProxyGenerator\RemoteObject\MethodGenerator\RemoteObjectMethod
     */
    public function testBodyStructureWithParameters() : void
    {
        /* @var $adapter PropertyGenerator|\PHPUnit_Framework_MockObject_MockObject */
        $adapter = $this->createMock(PropertyGenerator::class);
        $adapter->expects(self::any())->method('getName')->will(self::returnValue('adapter'));

        $reflectionMethod = new MethodReflection(
            BaseClass::class,
            'publicByReferenceParameterMethod'
        );

        $method = RemoteObjectMethod::generateMethod(
            $reflectionMethod,
            $adapter,
            new ReflectionClass(PropertyGenerator::class)
        );

        self::assertSame('publicByReferenceParameterMethod', $method->getName());
        self::assertCount(2, $method->getParameters());
        self::assertSame(
            '$return = $this->adapter->call(\'Zend\\\Code\\\Generator\\\PropertyGenerator\', '
            . '\'publicByReferenceParameterMethod\', array($param, $byRefParam));'
            . "\n\nreturn \$return;",
            $method->getBody()
        );
    }

    /**
     * @covers \ProxyManager\ProxyGenerator\RemoteObject\MethodGenerator\RemoteObjectMethod
     */
    public function testBodyStructureWithArrayParameter() : void
    {
        /* @var $adapter PropertyGenerator|\PHPUnit_Framework_MockObject_MockObject */
        $adapter = $this->createMock(PropertyGenerator::class);
        $adapter->expects(self::any())->method('getName')->will(self::returnValue('adapter'));

        $reflectionMethod = new MethodReflection(BaseClass::class, 'publicArrayHintedMethod');

        $method = RemoteObjectMethod::generateMethod(
            $reflectionMethod,
            $adapter,
            new ReflectionClass(PropertyGenerator::class)
        );

        self::assertSame('publicArrayHintedMethod', $method->getName());
        self::assertCount(1, $method->getParameters());
        self::assertSame(
            '$return = $this->adapter->call(\'Zend\\\Code\\\Generator\\\PropertyGenerator\', '
            . '\'publicArrayHintedMethod\', array($param));'
            . "\n\nreturn \$return;",
            $method->getBody()
        );
    }

    /**
     * @covers \ProxyManager\ProxyGenerator\RemoteObject\MethodGenerator\RemoteObjectMethod
     */
    public function testBodyStructureWithoutParameters() : void
    {
        /* @var $adapter PropertyGenerator|\PHPUnit_Framework_MockObject_MockObject */
        $adapter = $this->createMock(PropertyGenerator::class);
        $adapter->expects(self::any())->method('getName')->will(self::returnValue('adapter'));

        $reflectionMethod = new MethodReflection(BaseClass::class, 'publicMethod');

        $method = RemoteObjectMethod::generateMethod(
            $reflectionMethod,
            $adapter,
            new ReflectionClass(PropertyGenerator::class)
        );

        self::assertSame('publicMethod', $method->getName());
        self::assertCount(0, $method->getParameters());
        self::assertSame(
            '$return = $this->adapter->call(\'Zend\\\Code\\\Generator\\\PropertyGenerator\', '
            . '\'publicMethod\', array());'
            . "\n\nreturn \$return;",
            $method->getBody()
        );
    }
}
