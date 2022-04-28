<?php

declare(strict_types=1);

namespace ProxyManagerTest\ProxyGenerator\LazyLoadingGhost\MethodGenerator;

use Laminas\Code\Generator\PropertyGenerator;
use PHPUnit\Framework\TestCase;
use ProxyManager\ProxyGenerator\LazyLoadingGhost\MethodGenerator\CallInitializer;
use ProxyManager\ProxyGenerator\Util\Properties;
use ProxyManagerTestAsset\ClassWithMixedProperties;
use ProxyManagerTestAsset\ClassWithMixedTypedProperties;
use ReflectionClass;

/**
 * Tests for {@see \ProxyManager\ProxyGenerator\LazyLoadingGhost\MethodGenerator\CallInitializer}
 *
 * @group Coverage
 * @covers \ProxyManager\ProxyGenerator\LazyLoadingGhost\MethodGenerator\CallInitializer
 */
final class CallInitializerTest extends TestCase
{
    public function testBodyStructure(): void
    {
        $initializer           = $this->createMock(PropertyGenerator::class);
        $initializationTracker = $this->createMock(PropertyGenerator::class);

        $initializer->method('getName')->willReturn('init');
        $initializationTracker->method('getName')->willReturn('track');

        $callInitializer = new CallInitializer(
            $initializer,
            $initializationTracker,
            Properties::fromReflectionClass(new ReflectionClass(ClassWithMixedProperties::class))
        );

        $expectedCode = <<<'PHP'
if ($this->track || ! $this->init) {
    return;
}

$this->track = true;

$this->publicProperty0 = 'publicProperty0';
$this->publicProperty1 = 'publicProperty1';
$this->publicProperty2 = 'publicProperty2';
$this->protectedProperty0 = 'protectedProperty0';
$this->protectedProperty1 = 'protectedProperty1';
$this->protectedProperty2 = 'protectedProperty2';
static $cacheProxyManagerTestAsset_ClassWithMixedProperties;

$cacheProxyManagerTestAsset_ClassWithMixedProperties ?? $cacheProxyManagerTestAsset_ClassWithMixedProperties = \Closure::bind(static function ($instance) {
    $instance->privateProperty0 = 'privateProperty0';
    $instance->privateProperty1 = 'privateProperty1';
    $instance->privateProperty2 = 'privateProperty2';
}, null, 'ProxyManagerTestAsset\\ClassWithMixedProperties');

$cacheProxyManagerTestAsset_ClassWithMixedProperties($this);




$properties = [
    'publicProperty0' => & $this->publicProperty0,
    'publicProperty1' => & $this->publicProperty1,
    'publicProperty2' => & $this->publicProperty2,
    '' . "\0" . '*' . "\0" . 'protectedProperty0' => & $this->protectedProperty0,
    '' . "\0" . '*' . "\0" . 'protectedProperty1' => & $this->protectedProperty1,
    '' . "\0" . '*' . "\0" . 'protectedProperty2' => & $this->protectedProperty2,
];

static $cacheFetchProxyManagerTestAsset_ClassWithMixedProperties;

$cacheFetchProxyManagerTestAsset_ClassWithMixedProperties ?? $cacheFetchProxyManagerTestAsset_ClassWithMixedProperties = \Closure::bind(function ($instance, array & $properties) {
    $properties['' . "\0" . 'ProxyManagerTestAsset\\ClassWithMixedProperties' . "\0" . 'privateProperty0'] = & $instance->privateProperty0;
    $properties['' . "\0" . 'ProxyManagerTestAsset\\ClassWithMixedProperties' . "\0" . 'privateProperty1'] = & $instance->privateProperty1;
    $properties['' . "\0" . 'ProxyManagerTestAsset\\ClassWithMixedProperties' . "\0" . 'privateProperty2'] = & $instance->privateProperty2;
}, $this, 'ProxyManagerTestAsset\\ClassWithMixedProperties');

$cacheFetchProxyManagerTestAsset_ClassWithMixedProperties($this, $properties);

$result = $this->init->__invoke($this, $methodName, $parameters, $this->init, $properties);
$this->track = false;

return $result;
PHP;

        self::assertSame(
            $expectedCode,
            $callInitializer->getBody()
        );
    }

    public function testBodyStructureWithTypedProperties(): void
    {
        $initializer           = $this->createMock(PropertyGenerator::class);
        $initializationTracker = $this->createMock(PropertyGenerator::class);

        $initializer->method('getName')->willReturn('init');
        $initializationTracker->method('getName')->willReturn('track');

        $callInitializer = new CallInitializer(
            $initializer,
            $initializationTracker,
            Properties::fromReflectionClass(new ReflectionClass(ClassWithMixedTypedProperties::class))
        );

        $expectedCode = <<<'PHP'
if ($this->track || ! $this->init) {
    return;
}

$this->track = true;

$this->publicUnTypedProperty = 'publicUnTypedProperty';
$this->publicUnTypedPropertyWithoutDefaultValue = null;
$this->publicBoolProperty = true;
$this->publicNullableBoolProperty = true;
$this->publicNullableBoolPropertyWithoutDefaultValue = null;
$this->publicIntProperty = 123;
$this->publicNullableIntProperty = 123;
$this->publicNullableIntPropertyWithoutDefaultValue = null;
$this->publicFloatProperty = 123.456;
$this->publicNullableFloatProperty = 123.456;
$this->publicNullableFloatPropertyWithoutDefaultValue = null;
$this->publicStringProperty = 'publicStringProperty';
$this->publicNullableStringProperty = 'publicStringProperty';
$this->publicNullableStringPropertyWithoutDefaultValue = null;
$this->publicArrayProperty = ['publicArrayProperty'];
$this->publicNullableArrayProperty = ['publicArrayProperty'];
$this->publicNullableArrayPropertyWithoutDefaultValue = null;
$this->publicIterableProperty = ['publicIterableProperty'];
$this->publicNullableIterableProperty = ['publicIterableProperty'];
$this->publicNullableIterablePropertyWithoutDefaultValue = null;
$this->publicNullableObjectProperty = null;
$this->publicNullableClassProperty = null;
$this->protectedUnTypedProperty = 'protectedUnTypedProperty';
$this->protectedUnTypedPropertyWithoutDefaultValue = null;
$this->protectedBoolProperty = true;
$this->protectedNullableBoolProperty = true;
$this->protectedNullableBoolPropertyWithoutDefaultValue = null;
$this->protectedIntProperty = 123;
$this->protectedNullableIntProperty = 123;
$this->protectedNullableIntPropertyWithoutDefaultValue = null;
$this->protectedFloatProperty = 123.456;
$this->protectedNullableFloatProperty = 123.456;
$this->protectedNullableFloatPropertyWithoutDefaultValue = null;
$this->protectedStringProperty = 'protectedStringProperty';
$this->protectedNullableStringProperty = 'protectedStringProperty';
$this->protectedNullableStringPropertyWithoutDefaultValue = null;
$this->protectedArrayProperty = ['protectedArrayProperty'];
$this->protectedNullableArrayProperty = ['protectedArrayProperty'];
$this->protectedNullableArrayPropertyWithoutDefaultValue = null;
$this->protectedIterableProperty = ['protectedIterableProperty'];
$this->protectedNullableIterableProperty = ['protectedIterableProperty'];
$this->protectedNullableIterablePropertyWithoutDefaultValue = null;
$this->protectedNullableObjectProperty = null;
$this->protectedNullableClassProperty = null;
static $cacheProxyManagerTestAsset_ClassWithMixedTypedProperties;

$cacheProxyManagerTestAsset_ClassWithMixedTypedProperties ?? $cacheProxyManagerTestAsset_ClassWithMixedTypedProperties = \Closure::bind(static function ($instance) {
    $instance->privateUnTypedProperty = 'privateUnTypedProperty';
    $instance->privateUnTypedPropertyWithoutDefaultValue = null;
    $instance->privateBoolProperty = true;
    $instance->privateNullableBoolProperty = true;
    $instance->privateNullableBoolPropertyWithoutDefaultValue = null;
    $instance->privateIntProperty = 123;
    $instance->privateNullableIntProperty = 123;
    $instance->privateNullableIntPropertyWithoutDefaultValue = null;
    $instance->privateFloatProperty = 123.456;
    $instance->privateNullableFloatProperty = 123.456;
    $instance->privateNullableFloatPropertyWithoutDefaultValue = null;
    $instance->privateStringProperty = 'privateStringProperty';
    $instance->privateNullableStringProperty = 'privateStringProperty';
    $instance->privateNullableStringPropertyWithoutDefaultValue = null;
    $instance->privateArrayProperty = ['privateArrayProperty'];
    $instance->privateNullableArrayProperty = ['privateArrayProperty'];
    $instance->privateNullableArrayPropertyWithoutDefaultValue = null;
    $instance->privateIterableProperty = ['privateIterableProperty'];
    $instance->privateNullableIterableProperty = ['privateIterableProperty'];
    $instance->privateNullableIterablePropertyWithoutDefaultValue = null;
    $instance->privateNullableObjectProperty = null;
    $instance->privateNullableClassProperty = null;
}, null, 'ProxyManagerTestAsset\\ClassWithMixedTypedProperties');

$cacheProxyManagerTestAsset_ClassWithMixedTypedProperties($this);




$properties = [
    'publicUnTypedProperty' => & $this->publicUnTypedProperty,
    'publicUnTypedPropertyWithoutDefaultValue' => & $this->publicUnTypedPropertyWithoutDefaultValue,
    'publicBoolProperty' => & $this->publicBoolProperty,
    'publicNullableBoolProperty' => & $this->publicNullableBoolProperty,
    'publicNullableBoolPropertyWithoutDefaultValue' => & $this->publicNullableBoolPropertyWithoutDefaultValue,
    'publicIntProperty' => & $this->publicIntProperty,
    'publicNullableIntProperty' => & $this->publicNullableIntProperty,
    'publicNullableIntPropertyWithoutDefaultValue' => & $this->publicNullableIntPropertyWithoutDefaultValue,
    'publicFloatProperty' => & $this->publicFloatProperty,
    'publicNullableFloatProperty' => & $this->publicNullableFloatProperty,
    'publicNullableFloatPropertyWithoutDefaultValue' => & $this->publicNullableFloatPropertyWithoutDefaultValue,
    'publicStringProperty' => & $this->publicStringProperty,
    'publicNullableStringProperty' => & $this->publicNullableStringProperty,
    'publicNullableStringPropertyWithoutDefaultValue' => & $this->publicNullableStringPropertyWithoutDefaultValue,
    'publicArrayProperty' => & $this->publicArrayProperty,
    'publicNullableArrayProperty' => & $this->publicNullableArrayProperty,
    'publicNullableArrayPropertyWithoutDefaultValue' => & $this->publicNullableArrayPropertyWithoutDefaultValue,
    'publicIterableProperty' => & $this->publicIterableProperty,
    'publicNullableIterableProperty' => & $this->publicNullableIterableProperty,
    'publicNullableIterablePropertyWithoutDefaultValue' => & $this->publicNullableIterablePropertyWithoutDefaultValue,
    'publicNullableObjectProperty' => & $this->publicNullableObjectProperty,
    'publicNullableClassProperty' => & $this->publicNullableClassProperty,
    '' . "\0" . '*' . "\0" . 'protectedUnTypedProperty' => & $this->protectedUnTypedProperty,
    '' . "\0" . '*' . "\0" . 'protectedUnTypedPropertyWithoutDefaultValue' => & $this->protectedUnTypedPropertyWithoutDefaultValue,
    '' . "\0" . '*' . "\0" . 'protectedBoolProperty' => & $this->protectedBoolProperty,
    '' . "\0" . '*' . "\0" . 'protectedNullableBoolProperty' => & $this->protectedNullableBoolProperty,
    '' . "\0" . '*' . "\0" . 'protectedNullableBoolPropertyWithoutDefaultValue' => & $this->protectedNullableBoolPropertyWithoutDefaultValue,
    '' . "\0" . '*' . "\0" . 'protectedIntProperty' => & $this->protectedIntProperty,
    '' . "\0" . '*' . "\0" . 'protectedNullableIntProperty' => & $this->protectedNullableIntProperty,
    '' . "\0" . '*' . "\0" . 'protectedNullableIntPropertyWithoutDefaultValue' => & $this->protectedNullableIntPropertyWithoutDefaultValue,
    '' . "\0" . '*' . "\0" . 'protectedFloatProperty' => & $this->protectedFloatProperty,
    '' . "\0" . '*' . "\0" . 'protectedNullableFloatProperty' => & $this->protectedNullableFloatProperty,
    '' . "\0" . '*' . "\0" . 'protectedNullableFloatPropertyWithoutDefaultValue' => & $this->protectedNullableFloatPropertyWithoutDefaultValue,
    '' . "\0" . '*' . "\0" . 'protectedStringProperty' => & $this->protectedStringProperty,
    '' . "\0" . '*' . "\0" . 'protectedNullableStringProperty' => & $this->protectedNullableStringProperty,
    '' . "\0" . '*' . "\0" . 'protectedNullableStringPropertyWithoutDefaultValue' => & $this->protectedNullableStringPropertyWithoutDefaultValue,
    '' . "\0" . '*' . "\0" . 'protectedArrayProperty' => & $this->protectedArrayProperty,
    '' . "\0" . '*' . "\0" . 'protectedNullableArrayProperty' => & $this->protectedNullableArrayProperty,
    '' . "\0" . '*' . "\0" . 'protectedNullableArrayPropertyWithoutDefaultValue' => & $this->protectedNullableArrayPropertyWithoutDefaultValue,
    '' . "\0" . '*' . "\0" . 'protectedIterableProperty' => & $this->protectedIterableProperty,
    '' . "\0" . '*' . "\0" . 'protectedNullableIterableProperty' => & $this->protectedNullableIterableProperty,
    '' . "\0" . '*' . "\0" . 'protectedNullableIterablePropertyWithoutDefaultValue' => & $this->protectedNullableIterablePropertyWithoutDefaultValue,
    '' . "\0" . '*' . "\0" . 'protectedNullableObjectProperty' => & $this->protectedNullableObjectProperty,
    '' . "\0" . '*' . "\0" . 'protectedNullableClassProperty' => & $this->protectedNullableClassProperty,
];

static $cacheFetchProxyManagerTestAsset_ClassWithMixedTypedProperties;

$cacheFetchProxyManagerTestAsset_ClassWithMixedTypedProperties ?? $cacheFetchProxyManagerTestAsset_ClassWithMixedTypedProperties = \Closure::bind(function ($instance, array & $properties) {
    $properties['' . "\0" . 'ProxyManagerTestAsset\\ClassWithMixedTypedProperties' . "\0" . 'privateUnTypedProperty'] = & $instance->privateUnTypedProperty;
    $properties['' . "\0" . 'ProxyManagerTestAsset\\ClassWithMixedTypedProperties' . "\0" . 'privateUnTypedPropertyWithoutDefaultValue'] = & $instance->privateUnTypedPropertyWithoutDefaultValue;
    $properties['' . "\0" . 'ProxyManagerTestAsset\\ClassWithMixedTypedProperties' . "\0" . 'privateBoolProperty'] = & $instance->privateBoolProperty;
    $properties['' . "\0" . 'ProxyManagerTestAsset\\ClassWithMixedTypedProperties' . "\0" . 'privateNullableBoolProperty'] = & $instance->privateNullableBoolProperty;
    $properties['' . "\0" . 'ProxyManagerTestAsset\\ClassWithMixedTypedProperties' . "\0" . 'privateNullableBoolPropertyWithoutDefaultValue'] = & $instance->privateNullableBoolPropertyWithoutDefaultValue;
    $properties['' . "\0" . 'ProxyManagerTestAsset\\ClassWithMixedTypedProperties' . "\0" . 'privateIntProperty'] = & $instance->privateIntProperty;
    $properties['' . "\0" . 'ProxyManagerTestAsset\\ClassWithMixedTypedProperties' . "\0" . 'privateNullableIntProperty'] = & $instance->privateNullableIntProperty;
    $properties['' . "\0" . 'ProxyManagerTestAsset\\ClassWithMixedTypedProperties' . "\0" . 'privateNullableIntPropertyWithoutDefaultValue'] = & $instance->privateNullableIntPropertyWithoutDefaultValue;
    $properties['' . "\0" . 'ProxyManagerTestAsset\\ClassWithMixedTypedProperties' . "\0" . 'privateFloatProperty'] = & $instance->privateFloatProperty;
    $properties['' . "\0" . 'ProxyManagerTestAsset\\ClassWithMixedTypedProperties' . "\0" . 'privateNullableFloatProperty'] = & $instance->privateNullableFloatProperty;
    $properties['' . "\0" . 'ProxyManagerTestAsset\\ClassWithMixedTypedProperties' . "\0" . 'privateNullableFloatPropertyWithoutDefaultValue'] = & $instance->privateNullableFloatPropertyWithoutDefaultValue;
    $properties['' . "\0" . 'ProxyManagerTestAsset\\ClassWithMixedTypedProperties' . "\0" . 'privateStringProperty'] = & $instance->privateStringProperty;
    $properties['' . "\0" . 'ProxyManagerTestAsset\\ClassWithMixedTypedProperties' . "\0" . 'privateNullableStringProperty'] = & $instance->privateNullableStringProperty;
    $properties['' . "\0" . 'ProxyManagerTestAsset\\ClassWithMixedTypedProperties' . "\0" . 'privateNullableStringPropertyWithoutDefaultValue'] = & $instance->privateNullableStringPropertyWithoutDefaultValue;
    $properties['' . "\0" . 'ProxyManagerTestAsset\\ClassWithMixedTypedProperties' . "\0" . 'privateArrayProperty'] = & $instance->privateArrayProperty;
    $properties['' . "\0" . 'ProxyManagerTestAsset\\ClassWithMixedTypedProperties' . "\0" . 'privateNullableArrayProperty'] = & $instance->privateNullableArrayProperty;
    $properties['' . "\0" . 'ProxyManagerTestAsset\\ClassWithMixedTypedProperties' . "\0" . 'privateNullableArrayPropertyWithoutDefaultValue'] = & $instance->privateNullableArrayPropertyWithoutDefaultValue;
    $properties['' . "\0" . 'ProxyManagerTestAsset\\ClassWithMixedTypedProperties' . "\0" . 'privateIterableProperty'] = & $instance->privateIterableProperty;
    $properties['' . "\0" . 'ProxyManagerTestAsset\\ClassWithMixedTypedProperties' . "\0" . 'privateNullableIterableProperty'] = & $instance->privateNullableIterableProperty;
    $properties['' . "\0" . 'ProxyManagerTestAsset\\ClassWithMixedTypedProperties' . "\0" . 'privateNullableIterablePropertyWithoutDefaultValue'] = & $instance->privateNullableIterablePropertyWithoutDefaultValue;
    $properties['' . "\0" . 'ProxyManagerTestAsset\\ClassWithMixedTypedProperties' . "\0" . 'privateNullableObjectProperty'] = & $instance->privateNullableObjectProperty;
    $properties['' . "\0" . 'ProxyManagerTestAsset\\ClassWithMixedTypedProperties' . "\0" . 'privateNullableClassProperty'] = & $instance->privateNullableClassProperty;
}, $this, 'ProxyManagerTestAsset\\ClassWithMixedTypedProperties');

$cacheFetchProxyManagerTestAsset_ClassWithMixedTypedProperties($this, $properties);

$result = $this->init->__invoke($this, $methodName, $parameters, $this->init, $properties);
$this->track = false;

return $result;
PHP;

        self::assertSame(
            $expectedCode,
            $callInitializer->getBody()
        );
    }
}
