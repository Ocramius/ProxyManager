<?php

declare(strict_types=1);

namespace ProxyManagerTest\Exception;

use PHPUnit\Framework\TestCase;
use ProxyManager\Exception\UnsupportedProxiedClassException;
use ProxyManager\ProxyGenerator\Util\Properties;
use ProxyManagerTestAsset\ClassWithMixedTypedProperties;
use ProxyManagerTestAsset\ClassWithPrivateProperties;
use ReflectionClass;
use ReflectionProperty;

/**
 * Tests for {@see \ProxyManager\Exception\UnsupportedProxiedClassException}
 *
 * @covers \ProxyManager\Exception\UnsupportedProxiedClassException
 * @group Coverage
 */
final class UnsupportedProxiedClassExceptionTest extends TestCase
{
    public function testUnsupportedLocalizedReflectionProperty(): void
    {
        self::assertSame(
            'Provided reflection property "property0" of class "' . ClassWithPrivateProperties::class
            . '" is private and cannot be localized in PHP 5.3',
            UnsupportedProxiedClassException::unsupportedLocalizedReflectionProperty(
                new ReflectionProperty(ClassWithPrivateProperties::class, 'property0')
            )->getMessage()
        );
    }

    public function testNonReferenceableLocalizedReflectionProperties(): void
    {
        $reflectionClass = new ReflectionClass(ClassWithMixedTypedProperties::class);

        self::assertSame(
            'Cannot create references for following properties of class '
            . ClassWithMixedTypedProperties::class
            . ': publicBoolPropertyWithoutDefaultValue, '
            . 'publicIntPropertyWithoutDefaultValue, '
            . 'publicFloatPropertyWithoutDefaultValue, '
            . 'publicStringPropertyWithoutDefaultValue, '
            . 'publicArrayPropertyWithoutDefaultValue, '
            . 'publicIterablePropertyWithoutDefaultValue, '
            . 'publicObjectProperty, publicClassProperty, '
            . 'protectedBoolPropertyWithoutDefaultValue, '
            . 'protectedIntPropertyWithoutDefaultValue, '
            . 'protectedFloatPropertyWithoutDefaultValue, '
            . 'protectedStringPropertyWithoutDefaultValue, '
            . 'protectedArrayPropertyWithoutDefaultValue, '
            . 'protectedIterablePropertyWithoutDefaultValue, '
            . 'protectedObjectProperty, protectedClassProperty, '
            . 'privateBoolPropertyWithoutDefaultValue, '
            . 'privateIntPropertyWithoutDefaultValue, '
            . 'privateFloatPropertyWithoutDefaultValue, '
            . 'privateStringPropertyWithoutDefaultValue, '
            . 'privateArrayPropertyWithoutDefaultValue, '
            . 'privateIterablePropertyWithoutDefaultValue, '
            . 'privateObjectProperty, '
            . 'privateClassProperty',
            UnsupportedProxiedClassException::nonReferenceableLocalizedReflectionProperties(
                $reflectionClass,
                Properties::fromReflectionClass($reflectionClass)
                    ->onlyNonReferenceableProperties()
                    ->onlyInstanceProperties()
            )->getMessage()
        );
    }
}
