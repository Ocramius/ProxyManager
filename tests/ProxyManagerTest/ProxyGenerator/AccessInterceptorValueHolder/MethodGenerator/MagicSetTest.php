<?php

declare(strict_types=1);

namespace ProxyManagerTest\ProxyGenerator\AccessInterceptorValueHolder\MethodGenerator;

use Laminas\Code\Generator\PropertyGenerator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ProxyManager\ProxyGenerator\AccessInterceptorValueHolder\MethodGenerator\MagicSet;
use ProxyManager\ProxyGenerator\PropertyGenerator\PublicPropertiesMap;
use ProxyManagerTestAsset\EmptyClass;
use ReflectionClass;
use function strpos;

/**
 * Tests for {@see \ProxyManager\ProxyGenerator\AccessInterceptorValueHolder\MethodGenerator\MagicSet}
 *
 * @group Coverage
 */
final class MagicSetTest extends TestCase
{
    /**
     * @covers \ProxyManager\ProxyGenerator\AccessInterceptorValueHolder\MethodGenerator\MagicSet::__construct
     */
    public function testBodyStructure() : void
    {
        $reflection         = new ReflectionClass(EmptyClass::class);
        $valueHolder        = $this->createMock(PropertyGenerator::class);
        $prefixInterceptors = $this->createMock(PropertyGenerator::class);
        $suffixInterceptors = $this->createMock(PropertyGenerator::class);
        $publicProperties   = $this->createMock(PublicPropertiesMap::class);

        $valueHolder->method('getName')->willReturn('bar');
        $prefixInterceptors->method('getName')->willReturn('pre');
        $suffixInterceptors->method('getName')->willReturn('post');
        $publicProperties->method('isEmpty')->willReturn(false);

        $magicSet = new MagicSet(
            $reflection,
            $valueHolder,
            $prefixInterceptors,
            $suffixInterceptors,
            $publicProperties
        );

        self::assertSame('__set', $magicSet->getName());
        self::assertCount(2, $magicSet->getParameters());
        self::assertGreaterThan(0, strpos($magicSet->getBody(), '$returnValue = ($this->bar->$name = $value);'));
    }
}
