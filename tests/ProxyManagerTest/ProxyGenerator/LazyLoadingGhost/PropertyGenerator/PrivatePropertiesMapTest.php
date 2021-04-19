<?php

declare(strict_types=1);

namespace ProxyManagerTest\ProxyGenerator\LazyLoadingGhost\PropertyGenerator;

use Laminas\Code\Generator\PropertyGenerator;
use ProxyManager\ProxyGenerator\LazyLoadingGhost\PropertyGenerator\PrivatePropertiesMap;
use ProxyManager\ProxyGenerator\LazyLoadingGhost\PropertyGenerator\ProtectedPropertiesMap;
use ProxyManager\ProxyGenerator\Util\Properties;
use ProxyManagerTest\ProxyGenerator\PropertyGenerator\AbstractUniquePropertyNameTest;
use ProxyManagerTestAsset\ClassWithAbstractProtectedMethod;
use ProxyManagerTestAsset\ClassWithMixedProperties;
use ReflectionClass;

/**
 * Tests for {@see \ProxyManager\ProxyGenerator\LazyLoadingGhost\PropertyGenerator\PrivatePropertiesMap}
 *
 * @covers \ProxyManager\ProxyGenerator\LazyLoadingGhost\PropertyGenerator\PrivatePropertiesMap
 * @group Coverage
 */
final class PrivatePropertiesMapTest extends AbstractUniquePropertyNameTest
{
    protected function createProperty(): PropertyGenerator
    {
        return new PrivatePropertiesMap(
            Properties::fromReflectionClass(new ReflectionClass(ClassWithMixedProperties::class))
        );
    }

    public function testExtractsProtectedProperties(): void
    {
        $defaultValue = (new PrivatePropertiesMap(
            Properties::fromReflectionClass(new ReflectionClass(ClassWithMixedProperties::class))
        ))->getDefaultValue();

        self::assertNotNull($defaultValue);
        self::assertSame(
            [
                'privateProperty0' => [ClassWithMixedProperties::class => true],
                'privateProperty1' => [ClassWithMixedProperties::class => true],
                'privateProperty2' => [ClassWithMixedProperties::class => true],
            ],
            $defaultValue->getValue()
        );
    }

    public function testSkipsAbstractProtectedMethods(): void
    {
        $defaultValue = (new PrivatePropertiesMap(
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
