<?php

declare(strict_types=1);

namespace ProxyManagerTest\Generator\Util;

use PHPUnit\Framework\TestCase;
use ProxyManager\Generator\Util\ClassGeneratorUtils;
use ProxyManagerTestAsset\BaseClass;
use ProxyManagerTestAsset\ClassWithFinalMethods;
use ReflectionClass;
use Zend\Code\Generator\ClassGenerator;
use Zend\Code\Generator\MethodGenerator;

/**
 * Test to {@see ProxyManager\Generator\Util\ClassGeneratorUtils}
 *
 * @author Jefersson Nathan <malukenho@phpse.net>
 * @license MIT
 *
 * @covers ProxyManager\Generator\Util\ClassGeneratorUtils
 *
 * @group Coverage
 */
class ClassGeneratorUtilsTest extends TestCase
{
    public function testCantAddAFinalMethod() : void
    {
        /* @var $classGenerator ClassGenerator|\PHPUnit_Framework_MockObject_MockObject */
        $classGenerator  = $this->createMock(ClassGenerator::class);
        /* @var $methodGenerator MethodGenerator|\PHPUnit_Framework_MockObject_MockObject */
        $methodGenerator = $this->createMock(MethodGenerator::class);

        $methodGenerator
            ->expects(self::once())
            ->method('getName')
            ->willReturn('foo');

        $classGenerator
            ->expects(self::never())
            ->method('addMethodFromGenerator');

        $reflection = new ReflectionClass(ClassWithFinalMethods::class);

        self::assertFalse(ClassGeneratorUtils::addMethodIfNotFinal($reflection, $classGenerator, $methodGenerator));
    }

    public function testCanAddANotFinalMethod() : void
    {
        /* @var $classGenerator ClassGenerator|\PHPUnit_Framework_MockObject_MockObject */
        $classGenerator  = $this->createMock(ClassGenerator::class);
        /* @var $methodGenerator MethodGenerator|\PHPUnit_Framework_MockObject_MockObject */
        $methodGenerator = $this->createMock(MethodGenerator::class);

        $methodGenerator
            ->expects(self::once())
            ->method('getName')
            ->willReturn('publicMethod');

        $classGenerator
            ->expects(self::once())
            ->method('addMethodFromGenerator');

        $reflection = new ReflectionClass(BaseClass::class);

        self::assertTrue(ClassGeneratorUtils::addMethodIfNotFinal($reflection, $classGenerator, $methodGenerator));
    }
}
