<?php

declare(strict_types=1);

namespace ProxyManagerTest\ProxyGenerator\AccessInterceptorValueHolder\MethodGenerator;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ProxyManager\ProxyGenerator\AccessInterceptorValueHolder\MethodGenerator\MagicUnset;
use ProxyManager\ProxyGenerator\PropertyGenerator\PublicPropertiesMap;
use ProxyManagerTestAsset\EmptyClass;
use ReflectionClass;
use Zend\Code\Generator\PropertyGenerator;
use function strpos;

/**
 * Tests for {@see \ProxyManager\ProxyGenerator\AccessInterceptorValueHolder\MethodGenerator\MagicUnset}
 *
 * @group Coverage
 */
final class MagicUnsetTest extends TestCase
{
    /**
     * @covers \ProxyManager\ProxyGenerator\AccessInterceptorValueHolder\MethodGenerator\MagicUnset::__construct
     */
    public function testBodyStructure() : void
    {
        $reflection = new ReflectionClass(EmptyClass::class);
        /** @var PropertyGenerator&MockObject $valueHolder */
        $valueHolder = $this->createMock(PropertyGenerator::class);
        /** @var PropertyGenerator&MockObject $prefixInterceptors */
        $prefixInterceptors = $this->createMock(PropertyGenerator::class);
        /** @var PropertyGenerator&MockObject $suffixInterceptors */
        $suffixInterceptors = $this->createMock(PropertyGenerator::class);
        /** @var PublicPropertiesMap&MockObject $publicProperties */
        $publicProperties = $this
            ->getMockBuilder(PublicPropertiesMap::class)
            ->disableOriginalConstructor()
            ->getMock();

        $valueHolder->method('getName')->willReturn('bar');
        $prefixInterceptors->method('getName')->willReturn('pre');
        $suffixInterceptors->method('getName')->willReturn('post');
        $publicProperties->method('isEmpty')->willReturn(false);

        $magicUnset = new MagicUnset(
            $reflection,
            $valueHolder,
            $prefixInterceptors,
            $suffixInterceptors,
            $publicProperties
        );

        self::assertSame('__unset', $magicUnset->getName());
        self::assertCount(1, $magicUnset->getParameters());
        self::assertGreaterThan(
            0,
            strpos($magicUnset->getBody(), 'unset($this->bar->$name);')
        );
    }
}
