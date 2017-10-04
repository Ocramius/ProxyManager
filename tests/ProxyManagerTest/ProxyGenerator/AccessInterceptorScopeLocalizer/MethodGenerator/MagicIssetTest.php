<?php

declare(strict_types=1);

namespace ProxyManagerTest\ProxyGenerator\AccessInterceptorScopeLocalizer\MethodGenerator;

use PHPUnit_Framework_TestCase;
use ProxyManager\ProxyGenerator\AccessInterceptorScopeLocalizer\MethodGenerator\MagicIsset;
use ProxyManagerTestAsset\ClassWithMagicMethods;
use ProxyManagerTestAsset\EmptyClass;
use ReflectionClass;
use Zend\Code\Generator\PropertyGenerator;

/**
 * Tests for {@see \ProxyManager\ProxyGenerator\AccessInterceptorScopeLocalizer\MethodGenerator\MagicIsset}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 *
 * @group Coverage
 */
class MagicIssetTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers \ProxyManager\ProxyGenerator\AccessInterceptorScopeLocalizer\MethodGenerator\MagicIsset::__construct
     */
    public function testBodyStructure() : void
    {
        $reflection         = new ReflectionClass(EmptyClass::class);
        /* @var $prefixInterceptors PropertyGenerator|\PHPUnit_Framework_MockObject_MockObject */
        $prefixInterceptors = $this->createMock(PropertyGenerator::class);
        /* @var $suffixInterceptors PropertyGenerator|\PHPUnit_Framework_MockObject_MockObject */
        $suffixInterceptors = $this->createMock(PropertyGenerator::class);

        $prefixInterceptors->expects(self::any())->method('getName')->will(self::returnValue('pre'));
        $suffixInterceptors->expects(self::any())->method('getName')->will(self::returnValue('post'));

        $magicIsset = new MagicIsset(
            $reflection,
            $prefixInterceptors,
            $suffixInterceptors
        );

        self::assertSame('__isset', $magicIsset->getName());
        self::assertCount(1, $magicIsset->getParameters());
        self::assertStringMatchesFormat('%a$returnValue = $accessor();%a', $magicIsset->getBody());
    }

    /**
     * @covers \ProxyManager\ProxyGenerator\AccessInterceptorScopeLocalizer\MethodGenerator\MagicIsset::__construct
     */
    public function testBodyStructureWithInheritedMethod() : void
    {
        $reflection         = new ReflectionClass(ClassWithMagicMethods::class);
        /* @var $prefixInterceptors PropertyGenerator|\PHPUnit_Framework_MockObject_MockObject */
        $prefixInterceptors = $this->createMock(PropertyGenerator::class);
        /* @var $suffixInterceptors PropertyGenerator|\PHPUnit_Framework_MockObject_MockObject */
        $suffixInterceptors = $this->createMock(PropertyGenerator::class);

        $prefixInterceptors->expects(self::any())->method('getName')->will(self::returnValue('pre'));
        $suffixInterceptors->expects(self::any())->method('getName')->will(self::returnValue('post'));

        $magicIsset = new MagicIsset(
            $reflection,
            $prefixInterceptors,
            $suffixInterceptors
        );

        self::assertSame('__isset', $magicIsset->getName());
        self::assertCount(1, $magicIsset->getParameters());
        self::assertStringMatchesFormat('%a$returnValue = & parent::__isset($name);%a', $magicIsset->getBody());
    }
}
