<?php

declare(strict_types=1);

namespace ProxyManagerTest\ProxyGenerator\PropertyGenerator;

use PHPUnit\Framework\TestCase;
use ProxyManager\ProxyGenerator\PropertyGenerator\PublicPropertiesMap;
use ProxyManager\ProxyGenerator\Util\Properties;
use ProxyManagerTestAsset\ClassWithMixedProperties;
use ProxyManagerTestAsset\ClassWithPublicProperties;
use ProxyManagerTestAsset\EmptyClass;
use ReflectionClass;

/**
 * Tests for {@see \ProxyManager\ProxyGenerator\PropertyGenerator\PublicPropertiesMap}
 *
 * @covers \ProxyManager\ProxyGenerator\PropertyGenerator\PublicPropertiesMap
 * @group Coverage
 */
final class PublicPropertiesMapTest extends TestCase
{
    public function testEmptyClass(): void
    {
        $publicProperties = new PublicPropertiesMap(
            Properties::fromReflectionClass(new ReflectionClass(EmptyClass::class))
        );

        $defaultValue = $publicProperties->getDefaultValue();

        self::assertNotNull($defaultValue);
        self::assertIsArray($defaultValue->getValue());
        self::assertEmpty($defaultValue->getValue());
        self::assertTrue($publicProperties->isStatic());
        self::assertSame('private', $publicProperties->getVisibility());
        self::assertTrue($publicProperties->isEmpty());
    }

    public function testClassWithPublicProperties(): void
    {
        $publicProperties = new PublicPropertiesMap(
            Properties::fromReflectionClass(new ReflectionClass(ClassWithPublicProperties::class))
        );

        $defaultValue = $publicProperties->getDefaultValue();

        self::assertTrue($publicProperties->isStatic());
        self::assertSame('private', $publicProperties->getVisibility());
        self::assertFalse($publicProperties->isEmpty());
        self::assertNotNull($defaultValue);
        self::assertSame(
            [
                'property0' => true,
                'property1' => true,
                'property2' => true,
                'property3' => true,
                'property4' => true,
                'property5' => true,
                'property6' => true,
                'property7' => true,
                'property8' => true,
                'property9' => true,
            ],
            $defaultValue->getValue()
        );
    }

    public function testClassWithMixedProperties(): void
    {
        $defaultValue = (new PublicPropertiesMap(
            Properties::fromReflectionClass(new ReflectionClass(ClassWithMixedProperties::class))
        ))->getDefaultValue();

        self::assertNotNull($defaultValue);
        self::assertSame(
            [
                'publicProperty0' => true,
                'publicProperty1' => true,
                'publicProperty2' => true,
            ],
            $defaultValue->getValue()
        );
    }
}
