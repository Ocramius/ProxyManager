<?php

declare(strict_types=1);

namespace ProxyManagerTest\ProxyGenerator\Util;

use PHPUnit\Framework\TestCase;
use ProxyManager\ProxyGenerator\Util\Properties;
use ProxyManagerTestAsset\ClassWithAbstractProtectedMethod;
use ProxyManagerTestAsset\ClassWithAbstractPublicMethod;
use ProxyManagerTestAsset\ClassWithCollidingPrivateInheritedProperties;
use ProxyManagerTestAsset\ClassWithMixedProperties;
use ProxyManagerTestAsset\ClassWithMixedReferenceableTypedProperties;
use ProxyManagerTestAsset\ClassWithMixedTypedProperties;
use ProxyManagerTestAsset\ClassWithPrivateProperties;
use ReflectionClass;
use ReflectionProperty;
use function array_keys;
use function array_map;
use function array_values;

/**
 * Tests for {@see \ProxyManager\ProxyGenerator\Util\Properties}
 *
 * @covers \ProxyManager\ProxyGenerator\Util\Properties
 * @group  Coverage
 */
final class PropertiesTest extends TestCase
{
    public function testGetPublicProperties() : void
    {
        $properties       = Properties::fromReflectionClass(new ReflectionClass(ClassWithMixedProperties::class));
        $publicProperties = $properties->getPublicProperties();

        self::assertCount(3, $publicProperties);
        self::assertArrayHasKey('publicProperty0', $publicProperties);
        self::assertArrayHasKey('publicProperty1', $publicProperties);
        self::assertArrayHasKey('publicProperty2', $publicProperties);
    }

    public function testGetPublicPropertiesSkipsAbstractMethods() : void
    {
        $properties = Properties::fromReflectionClass(new ReflectionClass(ClassWithAbstractPublicMethod::class));

        self::assertEmpty($properties->getPublicProperties());
    }

    public function testGetProtectedProperties() : void
    {
        $properties = Properties::fromReflectionClass(new ReflectionClass(ClassWithMixedProperties::class));

        $protectedProperties = $properties->getProtectedProperties();

        self::assertCount(3, $protectedProperties);

        self::assertArrayHasKey("\0*\0protectedProperty0", $protectedProperties);
        self::assertArrayHasKey("\0*\0protectedProperty1", $protectedProperties);
        self::assertArrayHasKey("\0*\0protectedProperty2", $protectedProperties);
    }

    public function testOnlyNullableProperties() : void
    {
        $nullablePublicProperties = Properties::fromReflectionClass(new ReflectionClass(ClassWithMixedTypedProperties::class))
                                              ->onlyNullableProperties()
                                              ->getInstanceProperties();

        self::assertCount(48, $nullablePublicProperties);
        self::assertSame(
            [
                'publicUnTypedProperty',
                'publicUnTypedPropertyWithoutDefaultValue',
                'publicNullableBoolProperty',
                'publicNullableBoolPropertyWithoutDefaultValue',
                'publicNullableIntProperty',
                'publicNullableIntPropertyWithoutDefaultValue',
                'publicNullableFloatProperty',
                'publicNullableFloatPropertyWithoutDefaultValue',
                'publicNullableStringProperty',
                'publicNullableStringPropertyWithoutDefaultValue',
                'publicNullableArrayProperty',
                'publicNullableArrayPropertyWithoutDefaultValue',
                'publicNullableIterableProperty',
                'publicNullableIterablePropertyWithoutDefaultValue',
                'publicNullableObjectProperty',
                'publicNullableClassProperty',
                'protectedUnTypedProperty',
                'protectedUnTypedPropertyWithoutDefaultValue',
                'protectedNullableBoolProperty',
                'protectedNullableBoolPropertyWithoutDefaultValue',
                'protectedNullableIntProperty',
                'protectedNullableIntPropertyWithoutDefaultValue',
                'protectedNullableFloatProperty',
                'protectedNullableFloatPropertyWithoutDefaultValue',
                'protectedNullableStringProperty',
                'protectedNullableStringPropertyWithoutDefaultValue',
                'protectedNullableArrayProperty',
                'protectedNullableArrayPropertyWithoutDefaultValue',
                'protectedNullableIterableProperty',
                'protectedNullableIterablePropertyWithoutDefaultValue',
                'protectedNullableObjectProperty',
                'protectedNullableClassProperty',
                'privateUnTypedProperty',
                'privateUnTypedPropertyWithoutDefaultValue',
                'privateNullableBoolProperty',
                'privateNullableBoolPropertyWithoutDefaultValue',
                'privateNullableIntProperty',
                'privateNullableIntPropertyWithoutDefaultValue',
                'privateNullableFloatProperty',
                'privateNullableFloatPropertyWithoutDefaultValue',
                'privateNullableStringProperty',
                'privateNullableStringPropertyWithoutDefaultValue',
                'privateNullableArrayProperty',
                'privateNullableArrayPropertyWithoutDefaultValue',
                'privateNullableIterableProperty',
                'privateNullableIterablePropertyWithoutDefaultValue',
                'privateNullableObjectProperty',
                'privateNullableClassProperty',
            ],
            array_values(array_map(static function (ReflectionProperty $property) : string {
                return $property->getName();
            }, $nullablePublicProperties))
        );
    }

    public function testOnlyPropertiesThatCanBeUnset() : void
    {
        $nonReferenceableProperties = Properties::fromReflectionClass(new ReflectionClass(ClassWithMixedTypedProperties::class))
                                                ->onlyPropertiesThatCanBeUnset()
                                                ->getInstanceProperties();

        self::assertCount(42, $nonReferenceableProperties);
        self::assertSame(
            [
                'publicUnTypedProperty',
                'publicUnTypedPropertyWithoutDefaultValue',
                'publicBoolProperty',
                'publicNullableBoolProperty',
                'publicIntProperty',
                'publicNullableIntProperty',
                'publicFloatProperty',
                'publicNullableFloatProperty',
                'publicStringProperty',
                'publicNullableStringProperty',
                'publicArrayProperty',
                'publicNullableArrayProperty',
                'publicIterableProperty',
                'publicNullableIterableProperty',
                'protectedUnTypedProperty',
                'protectedUnTypedPropertyWithoutDefaultValue',
                'protectedBoolProperty',
                'protectedNullableBoolProperty',
                'protectedIntProperty',
                'protectedNullableIntProperty',
                'protectedFloatProperty',
                'protectedNullableFloatProperty',
                'protectedStringProperty',
                'protectedNullableStringProperty',
                'protectedArrayProperty',
                'protectedNullableArrayProperty',
                'protectedIterableProperty',
                'protectedNullableIterableProperty',
                'privateUnTypedProperty',
                'privateUnTypedPropertyWithoutDefaultValue',
                'privateBoolProperty',
                'privateNullableBoolProperty',
                'privateIntProperty',
                'privateNullableIntProperty',
                'privateFloatProperty',
                'privateNullableFloatProperty',
                'privateStringProperty',
                'privateNullableStringProperty',
                'privateArrayProperty',
                'privateNullableArrayProperty',
                'privateIterableProperty',
                'privateNullableIterableProperty',
            ],
            array_values(array_map(static function (ReflectionProperty $property) : string {
                return $property->getName();
            }, $nonReferenceableProperties))
        );
    }

    public function testOnlyNonReferenceableProperties() : void
    {
        self::assertTrue(
            Properties::fromReflectionClass(new ReflectionClass(ClassWithMixedReferenceableTypedProperties::class))
                      ->onlyNonReferenceableProperties()
                      ->empty()
        );

        $nonReferenceableProperties = Properties::fromReflectionClass(new ReflectionClass(ClassWithMixedTypedProperties::class))
                                                ->onlyNonReferenceableProperties()
                                                ->getInstanceProperties();

        self::assertCount(48, $nonReferenceableProperties);
        self::assertSame(
            [
                'publicBoolPropertyWithoutDefaultValue',
                'publicNullableBoolPropertyWithoutDefaultValue',
                'publicIntPropertyWithoutDefaultValue',
                'publicNullableIntPropertyWithoutDefaultValue',
                'publicFloatPropertyWithoutDefaultValue',
                'publicNullableFloatPropertyWithoutDefaultValue',
                'publicStringPropertyWithoutDefaultValue',
                'publicNullableStringPropertyWithoutDefaultValue',
                'publicArrayPropertyWithoutDefaultValue',
                'publicNullableArrayPropertyWithoutDefaultValue',
                'publicIterablePropertyWithoutDefaultValue',
                'publicNullableIterablePropertyWithoutDefaultValue',
                'publicObjectProperty',
                'publicNullableObjectProperty',
                'publicClassProperty',
                'publicNullableClassProperty',
                'protectedBoolPropertyWithoutDefaultValue',
                'protectedNullableBoolPropertyWithoutDefaultValue',
                'protectedIntPropertyWithoutDefaultValue',
                'protectedNullableIntPropertyWithoutDefaultValue',
                'protectedFloatPropertyWithoutDefaultValue',
                'protectedNullableFloatPropertyWithoutDefaultValue',
                'protectedStringPropertyWithoutDefaultValue',
                'protectedNullableStringPropertyWithoutDefaultValue',
                'protectedArrayPropertyWithoutDefaultValue',
                'protectedNullableArrayPropertyWithoutDefaultValue',
                'protectedIterablePropertyWithoutDefaultValue',
                'protectedNullableIterablePropertyWithoutDefaultValue',
                'protectedObjectProperty',
                'protectedNullableObjectProperty',
                'protectedClassProperty',
                'protectedNullableClassProperty',
                'privateBoolPropertyWithoutDefaultValue',
                'privateNullableBoolPropertyWithoutDefaultValue',
                'privateIntPropertyWithoutDefaultValue',
                'privateNullableIntPropertyWithoutDefaultValue',
                'privateFloatPropertyWithoutDefaultValue',
                'privateNullableFloatPropertyWithoutDefaultValue',
                'privateStringPropertyWithoutDefaultValue',
                'privateNullableStringPropertyWithoutDefaultValue',
                'privateArrayPropertyWithoutDefaultValue',
                'privateNullableArrayPropertyWithoutDefaultValue',
                'privateIterablePropertyWithoutDefaultValue',
                'privateNullableIterablePropertyWithoutDefaultValue',
                'privateObjectProperty',
                'privateNullableObjectProperty',
                'privateClassProperty',
                'privateNullableClassProperty',
            ],
            array_values(array_map(static function (ReflectionProperty $property) : string {
                return $property->getName();
            }, $nonReferenceableProperties))
        );
    }

    public function testGetProtectedPropertiesSkipsAbstractMethods() : void
    {
        $properties = Properties::fromReflectionClass(new ReflectionClass(ClassWithAbstractProtectedMethod::class));

        self::assertEmpty($properties->getProtectedProperties());
    }

    public function testGetPrivateProperties() : void
    {
        $properties = Properties::fromReflectionClass(new ReflectionClass(ClassWithMixedProperties::class));

        $privateProperties = $properties->getPrivateProperties();

        self::assertCount(3, $privateProperties);

        $prefix = "\0" . ClassWithMixedProperties::class . "\0";

        self::assertArrayHasKey($prefix . 'privateProperty0', $privateProperties);
        self::assertArrayHasKey($prefix . 'privateProperty1', $privateProperties);
        self::assertArrayHasKey($prefix . 'privateProperty2', $privateProperties);
    }

    public function testGetPrivatePropertiesFromInheritance() : void
    {
        $properties = Properties::fromReflectionClass(
            new ReflectionClass(ClassWithCollidingPrivateInheritedProperties::class)
        );

        $privateProperties = $properties->getPrivateProperties();

        self::assertCount(11, $privateProperties);

        $prefix = "\0" . ClassWithCollidingPrivateInheritedProperties::class . "\0";

        self::assertArrayHasKey($prefix . 'property0', $privateProperties);

        $prefix = "\0" . ClassWithPrivateProperties::class . "\0";

        self::assertArrayHasKey($prefix . 'property0', $privateProperties);
        self::assertArrayHasKey($prefix . 'property1', $privateProperties);
        self::assertArrayHasKey($prefix . 'property2', $privateProperties);
        self::assertArrayHasKey($prefix . 'property3', $privateProperties);
        self::assertArrayHasKey($prefix . 'property4', $privateProperties);
        self::assertArrayHasKey($prefix . 'property5', $privateProperties);
        self::assertArrayHasKey($prefix . 'property6', $privateProperties);
        self::assertArrayHasKey($prefix . 'property7', $privateProperties);
        self::assertArrayHasKey($prefix . 'property8', $privateProperties);
        self::assertArrayHasKey($prefix . 'property9', $privateProperties);
    }

    public function testGetAccessibleMethods() : void
    {
        $properties           = Properties::fromReflectionClass(new ReflectionClass(ClassWithMixedProperties::class));
        $accessibleProperties = $properties->getAccessibleProperties();

        self::assertCount(6, $accessibleProperties);
        self::assertArrayHasKey('publicProperty0', $accessibleProperties);
        self::assertArrayHasKey('publicProperty1', $accessibleProperties);
        self::assertArrayHasKey('publicProperty2', $accessibleProperties);
        self::assertArrayHasKey("\0*\0protectedProperty0", $accessibleProperties);
        self::assertArrayHasKey("\0*\0protectedProperty1", $accessibleProperties);
        self::assertArrayHasKey("\0*\0protectedProperty2", $accessibleProperties);
    }

    public function testGetGroupedPrivateProperties() : void
    {
        $properties     = Properties::fromReflectionClass(new ReflectionClass(ClassWithMixedProperties::class));
        $groupedPrivate = $properties->getGroupedPrivateProperties();

        self::assertCount(1, $groupedPrivate);

        $group = $groupedPrivate[ClassWithMixedProperties::class];

        self::assertCount(3, $group);

        self::assertArrayHasKey('privateProperty0', $group);
        self::assertArrayHasKey('privateProperty1', $group);
        self::assertArrayHasKey('privateProperty2', $group);
    }

    public function testGetGroupedPrivatePropertiesWithInheritedProperties() : void
    {
        $properties = Properties::fromReflectionClass(
            new ReflectionClass(ClassWithCollidingPrivateInheritedProperties::class)
        );

        $groupedPrivate = $properties->getGroupedPrivateProperties();

        self::assertCount(2, $groupedPrivate);

        $group1 = $groupedPrivate[ClassWithCollidingPrivateInheritedProperties::class];
        $group2 = $groupedPrivate[ClassWithPrivateProperties::class];

        self::assertCount(1, $group1);
        self::assertCount(10, $group2);

        self::assertArrayHasKey('property0', $group1);
        self::assertArrayHasKey('property0', $group2);
        self::assertArrayHasKey('property1', $group2);
        self::assertArrayHasKey('property2', $group2);
        self::assertArrayHasKey('property3', $group2);
        self::assertArrayHasKey('property4', $group2);
        self::assertArrayHasKey('property5', $group2);
        self::assertArrayHasKey('property6', $group2);
        self::assertArrayHasKey('property7', $group2);
        self::assertArrayHasKey('property8', $group2);
        self::assertArrayHasKey('property9', $group2);
    }

    public function testGetInstanceProperties() : void
    {
        $properties = Properties::fromReflectionClass(
            new ReflectionClass(ClassWithMixedProperties::class)
        );

        self::assertCount(9, $properties->getInstanceProperties());
    }

    /**
     * @param string $propertyName with property name
     *
     * @dataProvider propertiesToSkipFixture
     */
    public function testSkipPropertiesByFiltering(string $propertyName) : void
    {
        $properties = Properties::fromReflectionClass(
            new ReflectionClass(ClassWithMixedProperties::class)
        );

        self::assertArrayHasKey($propertyName, $properties->getInstanceProperties());
        $filteredProperties = $properties->filter([$propertyName]);

        self::assertArrayNotHasKey($propertyName, $filteredProperties->getInstanceProperties());
    }

    public function testSkipOverwritedPropertyUsingInheritance() : void
    {
        $propertyName = "\0ProxyManagerTestAsset\\ClassWithCollidingPrivateInheritedProperties\0property0";

        $properties = Properties::fromReflectionClass(
            new ReflectionClass(ClassWithCollidingPrivateInheritedProperties::class)
        );

        self::assertArrayHasKey($propertyName, $properties->getInstanceProperties());
        $filteredProperties = $properties->filter([$propertyName]);

        self::assertArrayNotHasKey($propertyName, $filteredProperties->getInstanceProperties());
    }

    public function testPropertiesIsSkippedFromRelatedMethods() : void
    {
        $properties = Properties::fromReflectionClass(
            new ReflectionClass(ClassWithMixedProperties::class)
        );

        self::assertArrayHasKey("\0*\0protectedProperty0", $properties->getProtectedProperties());
        self::assertArrayHasKey("\0*\0protectedProperty0", $properties->getInstanceProperties());
        $filteredProperties = $properties->filter(["\0*\0protectedProperty0"]);

        self::assertArrayNotHasKey("\0*\0protectedProperty0", $filteredProperties->getProtectedProperties());
        self::assertArrayNotHasKey("\0*\0protectedProperty0", $filteredProperties->getInstanceProperties());
    }

    /** @return string[][] */
    public function propertiesToSkipFixture() : array
    {
        return [
            ['publicProperty0'],
            ["\0*\0protectedProperty0"],
            ["\0ProxyManagerTestAsset\\ClassWithMixedProperties\0privateProperty0"],
        ];
    }

    public function testWithoutNonReferenceableProperties() : void
    {
        $properties = Properties::fromReflectionClass(new ReflectionClass(ClassWithMixedTypedProperties::class))
            ->withoutNonReferenceableProperties()
            ->getPublicProperties();

        self::assertSame(
            [
                'publicUnTypedProperty',
                'publicUnTypedPropertyWithoutDefaultValue',
                'publicBoolProperty',
                'publicNullableBoolProperty',
                'publicNullableBoolPropertyWithoutDefaultValue',
                'publicIntProperty',
                'publicNullableIntProperty',
                'publicNullableIntPropertyWithoutDefaultValue',
                'publicFloatProperty',
                'publicNullableFloatProperty',
                'publicNullableFloatPropertyWithoutDefaultValue',
                'publicStringProperty',
                'publicNullableStringProperty',
                'publicNullableStringPropertyWithoutDefaultValue',
                'publicArrayProperty',
                'publicNullableArrayProperty',
                'publicNullableArrayPropertyWithoutDefaultValue',
                'publicIterableProperty',
                'publicNullableIterableProperty',
                'publicNullableIterablePropertyWithoutDefaultValue',
                'publicNullableObjectProperty',
                'publicNullableClassProperty',
            ],
            array_keys($properties)
        );
    }
}
