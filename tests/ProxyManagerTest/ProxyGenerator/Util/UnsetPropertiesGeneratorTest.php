<?php

declare(strict_types=1);

namespace ProxyManagerTest\ProxyGenerator\Util;

use PHPUnit\Framework\TestCase;
use ProxyManager\ProxyGenerator\Util\Properties;
use ProxyManager\ProxyGenerator\Util\UnsetPropertiesGenerator;
use ProxyManagerTestAsset\BaseClass;
use ProxyManagerTestAsset\ClassWithCollidingPrivateInheritedProperties;
use ProxyManagerTestAsset\ClassWithMixedProperties;
use ProxyManagerTestAsset\ClassWithMixedTypedProperties;
use ProxyManagerTestAsset\EmptyClass;
use ReflectionClass;

/**
 * Tests for {@see \ProxyManager\ProxyGenerator\Util\UnsetPropertiesGenerator}
 *
 * @covers \ProxyManager\ProxyGenerator\Util\UnsetPropertiesGenerator
 * @group Coverage
 */
final class UnsetPropertiesGeneratorTest extends TestCase
{
    /**
     * @psalm-param class-string $className
     *
     * @dataProvider classNamesProvider
     */
    public function testGeneratedCode(string $className, string $expectedCode, string $instanceName): void
    {
        self::assertSame(
            $expectedCode,
            UnsetPropertiesGenerator::generateSnippet(
                Properties::fromReflectionClass(new ReflectionClass($className)),
                $instanceName
            )
        );
    }

    /**
     * @return string[][]
     */
    public function classNamesProvider(): array
    {
        return [
            EmptyClass::class => [
                EmptyClass::class,
                '',
                'foo',
            ],
            BaseClass::class => [
                BaseClass::class,
                'unset($foo->publicProperty, $foo->protectedProperty);

\Closure::bind(function (\ProxyManagerTestAsset\BaseClass $instance) {
    unset($instance->privateProperty);
}, $foo, \'ProxyManagerTestAsset\\\\BaseClass\')->__invoke($foo);

',
                'foo',
            ],
            ClassWithMixedProperties::class => [
                ClassWithMixedProperties::class,
                'unset($foo->publicProperty0, $foo->publicProperty1, $foo->publicProperty2, $foo->protectedProperty0, '
                . '$foo->protectedProperty1, $foo->protectedProperty2);

\Closure::bind(function (\ProxyManagerTestAsset\ClassWithMixedProperties $instance) {
    unset($instance->privateProperty0, $instance->privateProperty1, $instance->privateProperty2);
}, $foo, \'ProxyManagerTestAsset\\\\ClassWithMixedProperties\')->__invoke($foo);

',
                'foo',
            ],
            ClassWithCollidingPrivateInheritedProperties::class => [
                ClassWithCollidingPrivateInheritedProperties::class,
                '\Closure::bind(function (\ProxyManagerTestAsset\ClassWithCollidingPrivateInheritedProperties '
                . '$instance) {
    unset($instance->property0);
}, $bar, \'ProxyManagerTestAsset\\\\ClassWithCollidingPrivateInheritedProperties\')->__invoke($bar);

\Closure::bind(function (\ProxyManagerTestAsset\ClassWithPrivateProperties $instance) {
    unset($instance->property0, $instance->property1, $instance->property2, $instance->property3, '
                . '$instance->property4, $instance->property5, $instance->property6, $instance->property7, '
                . '$instance->property8, $instance->property9);
}, $bar, \'ProxyManagerTestAsset\\\\ClassWithPrivateProperties\')->__invoke($bar);

',
                'bar',
            ],
            ClassWithMixedTypedProperties::class => [
                ClassWithMixedTypedProperties::class,
                <<<'PHP'
unset($bar->publicUnTypedProperty, $bar->publicUnTypedPropertyWithoutDefaultValue, $bar->publicBoolProperty, $bar->publicBoolPropertyWithoutDefaultValue, $bar->publicNullableBoolProperty, $bar->publicNullableBoolPropertyWithoutDefaultValue, $bar->publicIntProperty, $bar->publicIntPropertyWithoutDefaultValue, $bar->publicNullableIntProperty, $bar->publicNullableIntPropertyWithoutDefaultValue, $bar->publicFloatProperty, $bar->publicFloatPropertyWithoutDefaultValue, $bar->publicNullableFloatProperty, $bar->publicNullableFloatPropertyWithoutDefaultValue, $bar->publicStringProperty, $bar->publicStringPropertyWithoutDefaultValue, $bar->publicNullableStringProperty, $bar->publicNullableStringPropertyWithoutDefaultValue, $bar->publicArrayProperty, $bar->publicArrayPropertyWithoutDefaultValue, $bar->publicNullableArrayProperty, $bar->publicNullableArrayPropertyWithoutDefaultValue, $bar->publicIterableProperty, $bar->publicIterablePropertyWithoutDefaultValue, $bar->publicNullableIterableProperty, $bar->publicNullableIterablePropertyWithoutDefaultValue, $bar->publicObjectProperty, $bar->publicNullableObjectProperty, $bar->publicClassProperty, $bar->publicNullableClassProperty, $bar->protectedUnTypedProperty, $bar->protectedUnTypedPropertyWithoutDefaultValue, $bar->protectedBoolProperty, $bar->protectedBoolPropertyWithoutDefaultValue, $bar->protectedNullableBoolProperty, $bar->protectedNullableBoolPropertyWithoutDefaultValue, $bar->protectedIntProperty, $bar->protectedIntPropertyWithoutDefaultValue, $bar->protectedNullableIntProperty, $bar->protectedNullableIntPropertyWithoutDefaultValue, $bar->protectedFloatProperty, $bar->protectedFloatPropertyWithoutDefaultValue, $bar->protectedNullableFloatProperty, $bar->protectedNullableFloatPropertyWithoutDefaultValue, $bar->protectedStringProperty, $bar->protectedStringPropertyWithoutDefaultValue, $bar->protectedNullableStringProperty, $bar->protectedNullableStringPropertyWithoutDefaultValue, $bar->protectedArrayProperty, $bar->protectedArrayPropertyWithoutDefaultValue, $bar->protectedNullableArrayProperty, $bar->protectedNullableArrayPropertyWithoutDefaultValue, $bar->protectedIterableProperty, $bar->protectedIterablePropertyWithoutDefaultValue, $bar->protectedNullableIterableProperty, $bar->protectedNullableIterablePropertyWithoutDefaultValue, $bar->protectedObjectProperty, $bar->protectedNullableObjectProperty, $bar->protectedClassProperty, $bar->protectedNullableClassProperty);

\Closure::bind(function (\ProxyManagerTestAsset\ClassWithMixedTypedProperties $instance) {
    unset($instance->privateUnTypedProperty, $instance->privateUnTypedPropertyWithoutDefaultValue, $instance->privateBoolProperty, $instance->privateBoolPropertyWithoutDefaultValue, $instance->privateNullableBoolProperty, $instance->privateNullableBoolPropertyWithoutDefaultValue, $instance->privateIntProperty, $instance->privateIntPropertyWithoutDefaultValue, $instance->privateNullableIntProperty, $instance->privateNullableIntPropertyWithoutDefaultValue, $instance->privateFloatProperty, $instance->privateFloatPropertyWithoutDefaultValue, $instance->privateNullableFloatProperty, $instance->privateNullableFloatPropertyWithoutDefaultValue, $instance->privateStringProperty, $instance->privateStringPropertyWithoutDefaultValue, $instance->privateNullableStringProperty, $instance->privateNullableStringPropertyWithoutDefaultValue, $instance->privateArrayProperty, $instance->privateArrayPropertyWithoutDefaultValue, $instance->privateNullableArrayProperty, $instance->privateNullableArrayPropertyWithoutDefaultValue, $instance->privateIterableProperty, $instance->privateIterablePropertyWithoutDefaultValue, $instance->privateNullableIterableProperty, $instance->privateNullableIterablePropertyWithoutDefaultValue, $instance->privateObjectProperty, $instance->privateNullableObjectProperty, $instance->privateClassProperty, $instance->privateNullableClassProperty);
}, $bar, 'ProxyManagerTestAsset\\ClassWithMixedTypedProperties')->__invoke($bar);


PHP,
                'bar',
            ],
        ];
    }
}
