<?php

declare(strict_types=1);

namespace ProxyManagerTest\ProxyGenerator\AccessInterceptorValueHolder\MethodGenerator;

use PHPUnit\Framework\TestCase;
use ProxyManager\ProxyGenerator\AccessInterceptorValueHolder\MethodGenerator\MagicIsset;
use ProxyManager\ProxyGenerator\PropertyGenerator\PublicPropertiesMap;
use ProxyManagerTestAsset\EmptyClass;
use ReflectionClass;
use Zend\Code\Generator\PropertyGenerator;
use function strpos;

/**
 * Tests for {@see \ProxyManager\ProxyGenerator\AccessInterceptorValueHolder\MethodGenerator\MagicIsset}
 *
 * @group Coverage
 */
class MagicIssetTest extends TestCase
{
    /**
     * @covers \ProxyManager\ProxyGenerator\AccessInterceptorValueHolder\MethodGenerator\MagicIsset::__construct
     */
    public function testBodyStructure() : void
    {
        $reflection = new ReflectionClass(EmptyClass::class);
        /** @var PropertyGenerator|\PHPUnit_Framework_MockObject_MockObject $valueHolder */
        $valueHolder = $this->createMock(PropertyGenerator::class);
        /** @var PropertyGenerator|\PHPUnit_Framework_MockObject_MockObject $prefixInterceptors */
        $prefixInterceptors = $this->createMock(PropertyGenerator::class);
        /** @var PropertyGenerator|\PHPUnit_Framework_MockObject_MockObject $suffixInterceptors */
        $suffixInterceptors = $this->createMock(PropertyGenerator::class);
        /** @var PublicPropertiesMap|\PHPUnit_Framework_MockObject_MockObject $publicProperties */
        $publicProperties = $this
            ->getMockBuilder(PublicPropertiesMap::class)
            ->disableOriginalConstructor()
            ->getMock();

        $valueHolder->expects(self::any())->method('getName')->will(self::returnValue('bar'));
        $prefixInterceptors->expects(self::any())->method('getName')->will(self::returnValue('pre'));
        $suffixInterceptors->expects(self::any())->method('getName')->will(self::returnValue('post'));
        $publicProperties->expects(self::any())->method('isEmpty')->will(self::returnValue(false));

        $magicIsset = new MagicIsset(
            $reflection,
            $valueHolder,
            $prefixInterceptors,
            $suffixInterceptors,
            $publicProperties
        );

        self::assertSame('__isset', $magicIsset->getName());
        self::assertCount(1, $magicIsset->getParameters());
        self::assertGreaterThan(0, strpos($magicIsset->getBody(), '$returnValue = isset($this->bar->$name);'));
    }
}
