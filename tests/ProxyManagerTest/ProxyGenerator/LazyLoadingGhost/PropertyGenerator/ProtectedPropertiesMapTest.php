<?php

declare(strict_types=1);

namespace ProxyManagerTest\ProxyGenerator\LazyLoadingGhost\PropertyGenerator;

use Laminas\Code\Generator\PropertyGenerator;
use ProxyManager\ProxyGenerator\LazyLoadingGhost\PropertyGenerator\ProtectedPropertiesMap;
use ProxyManager\ProxyGenerator\Util\Properties;
use ProxyManagerTest\ProxyGenerator\PropertyGenerator\AbstractUniquePropertyNameTest;
use ProxyManagerTestAsset\ClassWithAbstractProtectedMethod;
use ProxyManagerTestAsset\ClassWithMixedProperties;
use ReflectionClass;

/**
 * Tests for {@see \ProxyManager\ProxyGenerator\LazyLoadingGhost\PropertyGenerator\ProtectedPropertiesMap}
 *
 * @covers \ProxyManager\ProxyGenerator\LazyLoadingGhost\PropertyGenerator\ProtectedPropertiesMap
 * @group Coverage
 */
final class ProtectedPropertiesMapTest extends AbstractUniquePropertyNameTest
{
    protected function createProperty(): PropertyGenerator
    {
        return new ProtectedPropertiesMap(
            Properties::fromReflectionClass(new ReflectionClass(ClassWithMixedProperties::class))
        );
    }

    public function testExtractsProtectedProperties(): void
    {
        $defaultValue = (new ProtectedPropertiesMap(
            Properties::fromReflectionClass(new ReflectionClass(ClassWithMixedProperties::class))
        ))->getDefaultValue();

        self::assertNotNull($defaultValue);
        self::assertSame(
            [
                'protectedProperty0' => ClassWithMixedProperties::class,
                'protectedProperty1' => ClassWithMixedProperties::class,
                'protectedProperty2' => ClassWithMixedProperties::class,
            ],
            $defaultValue->getValue()
        );
    }

    public function testSkipsAbstractProtectedMethods(): void
    {
        $defaultValue = (new ProtectedPropertiesMap(
            Properties::fromReflectionClass(new ReflectionClass(ClassWithAbstractProtectedMethod::class))
        ))->getDefaultValue();

        self::assertNotNull($defaultValue);
        self::assertSame([], $defaultValue->getValue());
    }

    public function testIsStaticPrivate(): void
    {
        $map = $this->createProperty();

        self::assertTrue($map->isStatic());
        self::assertSame(ProtectedPropertiesMap::VISIBILITY_PRIVATE, $map->getVisibility());
    }
}
