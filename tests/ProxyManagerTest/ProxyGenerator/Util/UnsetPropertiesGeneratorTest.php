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
     * @dataProvider classNamesProvider
     * @psalm-param class-string $className
     */
    public function testGeneratedCode(string $className, string $expectedCode, string $instanceName) : void
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
    public function classNamesProvider() : array
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
unset($bar->publicUnTypedProperty, $bar->publicUnTypedPropertyWithoutDefaultValue, $bar->publicBoolProperty, $bar->publicNullableBoolProperty, $bar->publicIntProperty, $bar->publicNullableIntProperty, $bar->publicFloatProperty, $bar->publicNullableFloatProperty, $bar->publicStringProperty, $bar->publicNullableStringProperty, $bar->publicArrayProperty, $bar->publicNullableArrayProperty, $bar->publicIterableProperty, $bar->publicNullableIterableProperty, $bar->protectedUnTypedProperty, $bar->protectedUnTypedPropertyWithoutDefaultValue, $bar->protectedBoolProperty, $bar->protectedNullableBoolProperty, $bar->protectedIntProperty, $bar->protectedNullableIntProperty, $bar->protectedFloatProperty, $bar->protectedNullableFloatProperty, $bar->protectedStringProperty, $bar->protectedNullableStringProperty, $bar->protectedArrayProperty, $bar->protectedNullableArrayProperty, $bar->protectedIterableProperty, $bar->protectedNullableIterableProperty);

\Closure::bind(function (\ProxyManagerTestAsset\ClassWithMixedTypedProperties $instance) {
    unset($instance->privateUnTypedProperty, $instance->privateUnTypedPropertyWithoutDefaultValue, $instance->privateBoolProperty, $instance->privateNullableBoolProperty, $instance->privateIntProperty, $instance->privateNullableIntProperty, $instance->privateFloatProperty, $instance->privateNullableFloatProperty, $instance->privateStringProperty, $instance->privateNullableStringProperty, $instance->privateArrayProperty, $instance->privateNullableArrayProperty, $instance->privateIterableProperty, $instance->privateNullableIterableProperty);
}, $bar, 'ProxyManagerTestAsset\\ClassWithMixedTypedProperties')->__invoke($bar);


PHP,
                'bar',
            ],
        ];
    }
}
