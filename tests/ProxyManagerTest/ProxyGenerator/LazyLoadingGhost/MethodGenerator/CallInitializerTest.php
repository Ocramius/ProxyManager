<?php

declare(strict_types=1);

namespace ProxyManagerTest\ProxyGenerator\LazyLoadingGhost\MethodGenerator;

use Laminas\Code\Generator\PropertyGenerator;
use PHPUnit\Framework\TestCase;
use ProxyManager\ProxyGenerator\LazyLoadingGhost\MethodGenerator\CallInitializer;
use ProxyManager\ProxyGenerator\Util\Properties;
use ProxyManagerTestAsset\ClassWithMixedProperties;
use ProxyManagerTestAsset\ClassWithMixedTypedProperties;
use ProxyManagerTestAsset\ClassWithReadOnlyProperties;
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
}, null, 'ProxyManagerTestAsset\\ClassWithMixedProperties');

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
$this->publicUnTypedPropertyWithoutDefaultValue = NULL;
$this->publicBoolProperty = true;
$this->publicNullableBoolProperty = true;
$this->publicNullableBoolPropertyWithoutDefaultValue = NULL;
$this->publicIntProperty = 123;
$this->publicNullableIntProperty = 123;
$this->publicNullableIntPropertyWithoutDefaultValue = NULL;
$this->publicFloatProperty = 123.456;
$this->publicNullableFloatProperty = 123.456;
$this->publicNullableFloatPropertyWithoutDefaultValue = NULL;
$this->publicStringProperty = 'publicStringProperty';
$this->publicNullableStringProperty = 'publicStringProperty';
$this->publicNullableStringPropertyWithoutDefaultValue = NULL;
$this->publicArrayProperty = array (
  0 => 'publicArrayProperty',
);
$this->publicNullableArrayProperty = array (
  0 => 'publicArrayProperty',
);
$this->publicNullableArrayPropertyWithoutDefaultValue = NULL;
$this->publicIterableProperty = array (
  0 => 'publicIterableProperty',
);
$this->publicNullableIterableProperty = array (
  0 => 'publicIterableProperty',
);
$this->publicNullableIterablePropertyWithoutDefaultValue = NULL;
$this->publicNullableObjectProperty = NULL;
$this->publicNullableClassProperty = NULL;
$this->protectedUnTypedProperty = 'protectedUnTypedProperty';
$this->protectedUnTypedPropertyWithoutDefaultValue = NULL;
$this->protectedBoolProperty = true;
$this->protectedNullableBoolProperty = true;
$this->protectedNullableBoolPropertyWithoutDefaultValue = NULL;
$this->protectedIntProperty = 123;
$this->protectedNullableIntProperty = 123;
$this->protectedNullableIntPropertyWithoutDefaultValue = NULL;
$this->protectedFloatProperty = 123.456;
$this->protectedNullableFloatProperty = 123.456;
$this->protectedNullableFloatPropertyWithoutDefaultValue = NULL;
$this->protectedStringProperty = 'protectedStringProperty';
$this->protectedNullableStringProperty = 'protectedStringProperty';
$this->protectedNullableStringPropertyWithoutDefaultValue = NULL;
$this->protectedArrayProperty = array (
  0 => 'protectedArrayProperty',
);
$this->protectedNullableArrayProperty = array (
  0 => 'protectedArrayProperty',
);
$this->protectedNullableArrayPropertyWithoutDefaultValue = NULL;
$this->protectedIterableProperty = array (
  0 => 'protectedIterableProperty',
);
$this->protectedNullableIterableProperty = array (
  0 => 'protectedIterableProperty',
);
$this->protectedNullableIterablePropertyWithoutDefaultValue = NULL;
$this->protectedNullableObjectProperty = NULL;
$this->protectedNullableClassProperty = NULL;
static $cacheProxyManagerTestAsset_ClassWithMixedTypedProperties;

$cacheProxyManagerTestAsset_ClassWithMixedTypedProperties ?? $cacheProxyManagerTestAsset_ClassWithMixedTypedProperties = \Closure::bind(static function ($instance) {
    $instance->privateUnTypedProperty = 'privateUnTypedProperty';
    $instance->privateUnTypedPropertyWithoutDefaultValue = NULL;
    $instance->privateBoolProperty = true;
    $instance->privateNullableBoolProperty = true;
    $instance->privateNullableBoolPropertyWithoutDefaultValue = NULL;
    $instance->privateIntProperty = 123;
    $instance->privateNullableIntProperty = 123;
    $instance->privateNullableIntPropertyWithoutDefaultValue = NULL;
    $instance->privateFloatProperty = 123.456;
    $instance->privateNullableFloatProperty = 123.456;
    $instance->privateNullableFloatPropertyWithoutDefaultValue = NULL;
    $instance->privateStringProperty = 'privateStringProperty';
    $instance->privateNullableStringProperty = 'privateStringProperty';
    $instance->privateNullableStringPropertyWithoutDefaultValue = NULL;
    $instance->privateArrayProperty = array (
  0 => 'privateArrayProperty',
);
    $instance->privateNullableArrayProperty = array (
  0 => 'privateArrayProperty',
);
    $instance->privateNullableArrayPropertyWithoutDefaultValue = NULL;
    $instance->privateIterableProperty = array (
  0 => 'privateIterableProperty',
);
    $instance->privateNullableIterableProperty = array (
  0 => 'privateIterableProperty',
);
    $instance->privateNullableIterablePropertyWithoutDefaultValue = NULL;
    $instance->privateNullableObjectProperty = NULL;
    $instance->privateNullableClassProperty = NULL;
}, null, 'ProxyManagerTestAsset\\ClassWithMixedTypedProperties');

$cacheProxyManagerTestAsset_ClassWithMixedTypedProperties($this);




$nonReferenceableProperties = new class() {
    public ?bool $publicBoolPropertyWithoutDefaultValue;
    public ?int $publicIntPropertyWithoutDefaultValue;
    public ?float $publicFloatPropertyWithoutDefaultValue;
    public ?string $publicStringPropertyWithoutDefaultValue;
    public ?array $publicArrayPropertyWithoutDefaultValue;
    public ?iterable $publicIterablePropertyWithoutDefaultValue;
    public ?object $publicObjectProperty;
    public ?\ProxyManagerTestAsset\EmptyClass $publicClassProperty;
    public ?bool $protectedBoolPropertyWithoutDefaultValue;
    public ?int $protectedIntPropertyWithoutDefaultValue;
    public ?float $protectedFloatPropertyWithoutDefaultValue;
    public ?string $protectedStringPropertyWithoutDefaultValue;
    public ?array $protectedArrayPropertyWithoutDefaultValue;
    public ?iterable $protectedIterablePropertyWithoutDefaultValue;
    public ?object $protectedObjectProperty;
    public ?\ProxyManagerTestAsset\EmptyClass $protectedClassProperty;
    public ?bool $privateBoolPropertyWithoutDefaultValue_on_ProxyManagerTestAsset_ClassWithMixedTypedProperties;
    public ?int $privateIntPropertyWithoutDefaultValue_on_ProxyManagerTestAsset_ClassWithMixedTypedProperties;
    public ?float $privateFloatPropertyWithoutDefaultValue_on_ProxyManagerTestAsset_ClassWithMixedTypedProperties;
    public ?string $privateStringPropertyWithoutDefaultValue_on_ProxyManagerTestAsset_ClassWithMixedTypedProperties;
    public ?array $privateArrayPropertyWithoutDefaultValue_on_ProxyManagerTestAsset_ClassWithMixedTypedProperties;
    public ?iterable $privateIterablePropertyWithoutDefaultValue_on_ProxyManagerTestAsset_ClassWithMixedTypedProperties;
    public ?object $privateObjectProperty_on_ProxyManagerTestAsset_ClassWithMixedTypedProperties;
    public ?\ProxyManagerTestAsset\EmptyClass $privateClassProperty_on_ProxyManagerTestAsset_ClassWithMixedTypedProperties;
};
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
    'publicBoolPropertyWithoutDefaultValue' => & $nonReferenceableProperties->publicBoolPropertyWithoutDefaultValue,
    'publicIntPropertyWithoutDefaultValue' => & $nonReferenceableProperties->publicIntPropertyWithoutDefaultValue,
    'publicFloatPropertyWithoutDefaultValue' => & $nonReferenceableProperties->publicFloatPropertyWithoutDefaultValue,
    'publicStringPropertyWithoutDefaultValue' => & $nonReferenceableProperties->publicStringPropertyWithoutDefaultValue,
    'publicArrayPropertyWithoutDefaultValue' => & $nonReferenceableProperties->publicArrayPropertyWithoutDefaultValue,
    'publicIterablePropertyWithoutDefaultValue' => & $nonReferenceableProperties->publicIterablePropertyWithoutDefaultValue,
    'publicObjectProperty' => & $nonReferenceableProperties->publicObjectProperty,
    'publicClassProperty' => & $nonReferenceableProperties->publicClassProperty,
    '' . "\0" . '*' . "\0" . 'protectedBoolPropertyWithoutDefaultValue' => & $nonReferenceableProperties->protectedBoolPropertyWithoutDefaultValue,
    '' . "\0" . '*' . "\0" . 'protectedIntPropertyWithoutDefaultValue' => & $nonReferenceableProperties->protectedIntPropertyWithoutDefaultValue,
    '' . "\0" . '*' . "\0" . 'protectedFloatPropertyWithoutDefaultValue' => & $nonReferenceableProperties->protectedFloatPropertyWithoutDefaultValue,
    '' . "\0" . '*' . "\0" . 'protectedStringPropertyWithoutDefaultValue' => & $nonReferenceableProperties->protectedStringPropertyWithoutDefaultValue,
    '' . "\0" . '*' . "\0" . 'protectedArrayPropertyWithoutDefaultValue' => & $nonReferenceableProperties->protectedArrayPropertyWithoutDefaultValue,
    '' . "\0" . '*' . "\0" . 'protectedIterablePropertyWithoutDefaultValue' => & $nonReferenceableProperties->protectedIterablePropertyWithoutDefaultValue,
    '' . "\0" . '*' . "\0" . 'protectedObjectProperty' => & $nonReferenceableProperties->protectedObjectProperty,
    '' . "\0" . '*' . "\0" . 'protectedClassProperty' => & $nonReferenceableProperties->protectedClassProperty,
    '' . "\0" . 'ProxyManagerTestAsset\\ClassWithMixedTypedProperties' . "\0" . 'privateBoolPropertyWithoutDefaultValue' => & $nonReferenceableProperties->privateBoolPropertyWithoutDefaultValue_on_ProxyManagerTestAsset_ClassWithMixedTypedProperties,
    '' . "\0" . 'ProxyManagerTestAsset\\ClassWithMixedTypedProperties' . "\0" . 'privateIntPropertyWithoutDefaultValue' => & $nonReferenceableProperties->privateIntPropertyWithoutDefaultValue_on_ProxyManagerTestAsset_ClassWithMixedTypedProperties,
    '' . "\0" . 'ProxyManagerTestAsset\\ClassWithMixedTypedProperties' . "\0" . 'privateFloatPropertyWithoutDefaultValue' => & $nonReferenceableProperties->privateFloatPropertyWithoutDefaultValue_on_ProxyManagerTestAsset_ClassWithMixedTypedProperties,
    '' . "\0" . 'ProxyManagerTestAsset\\ClassWithMixedTypedProperties' . "\0" . 'privateStringPropertyWithoutDefaultValue' => & $nonReferenceableProperties->privateStringPropertyWithoutDefaultValue_on_ProxyManagerTestAsset_ClassWithMixedTypedProperties,
    '' . "\0" . 'ProxyManagerTestAsset\\ClassWithMixedTypedProperties' . "\0" . 'privateArrayPropertyWithoutDefaultValue' => & $nonReferenceableProperties->privateArrayPropertyWithoutDefaultValue_on_ProxyManagerTestAsset_ClassWithMixedTypedProperties,
    '' . "\0" . 'ProxyManagerTestAsset\\ClassWithMixedTypedProperties' . "\0" . 'privateIterablePropertyWithoutDefaultValue' => & $nonReferenceableProperties->privateIterablePropertyWithoutDefaultValue_on_ProxyManagerTestAsset_ClassWithMixedTypedProperties,
    '' . "\0" . 'ProxyManagerTestAsset\\ClassWithMixedTypedProperties' . "\0" . 'privateObjectProperty' => & $nonReferenceableProperties->privateObjectProperty_on_ProxyManagerTestAsset_ClassWithMixedTypedProperties,
    '' . "\0" . 'ProxyManagerTestAsset\\ClassWithMixedTypedProperties' . "\0" . 'privateClassProperty' => & $nonReferenceableProperties->privateClassProperty_on_ProxyManagerTestAsset_ClassWithMixedTypedProperties,
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
}, null, 'ProxyManagerTestAsset\\ClassWithMixedTypedProperties');

$cacheFetchProxyManagerTestAsset_ClassWithMixedTypedProperties($this, $properties);

$result = $this->init->__invoke($this, $methodName, $parameters, $this->init, $properties);
isset($nonReferenceableProperties->publicBoolPropertyWithoutDefaultValue) && $this->publicBoolPropertyWithoutDefaultValue = $nonReferenceableProperties->publicBoolPropertyWithoutDefaultValue;
isset($nonReferenceableProperties->publicIntPropertyWithoutDefaultValue) && $this->publicIntPropertyWithoutDefaultValue = $nonReferenceableProperties->publicIntPropertyWithoutDefaultValue;
isset($nonReferenceableProperties->publicFloatPropertyWithoutDefaultValue) && $this->publicFloatPropertyWithoutDefaultValue = $nonReferenceableProperties->publicFloatPropertyWithoutDefaultValue;
isset($nonReferenceableProperties->publicStringPropertyWithoutDefaultValue) && $this->publicStringPropertyWithoutDefaultValue = $nonReferenceableProperties->publicStringPropertyWithoutDefaultValue;
isset($nonReferenceableProperties->publicArrayPropertyWithoutDefaultValue) && $this->publicArrayPropertyWithoutDefaultValue = $nonReferenceableProperties->publicArrayPropertyWithoutDefaultValue;
isset($nonReferenceableProperties->publicIterablePropertyWithoutDefaultValue) && $this->publicIterablePropertyWithoutDefaultValue = $nonReferenceableProperties->publicIterablePropertyWithoutDefaultValue;
isset($nonReferenceableProperties->publicObjectProperty) && $this->publicObjectProperty = $nonReferenceableProperties->publicObjectProperty;
isset($nonReferenceableProperties->publicClassProperty) && $this->publicClassProperty = $nonReferenceableProperties->publicClassProperty;
isset($nonReferenceableProperties->protectedBoolPropertyWithoutDefaultValue) && $this->protectedBoolPropertyWithoutDefaultValue = $nonReferenceableProperties->protectedBoolPropertyWithoutDefaultValue;
isset($nonReferenceableProperties->protectedIntPropertyWithoutDefaultValue) && $this->protectedIntPropertyWithoutDefaultValue = $nonReferenceableProperties->protectedIntPropertyWithoutDefaultValue;
isset($nonReferenceableProperties->protectedFloatPropertyWithoutDefaultValue) && $this->protectedFloatPropertyWithoutDefaultValue = $nonReferenceableProperties->protectedFloatPropertyWithoutDefaultValue;
isset($nonReferenceableProperties->protectedStringPropertyWithoutDefaultValue) && $this->protectedStringPropertyWithoutDefaultValue = $nonReferenceableProperties->protectedStringPropertyWithoutDefaultValue;
isset($nonReferenceableProperties->protectedArrayPropertyWithoutDefaultValue) && $this->protectedArrayPropertyWithoutDefaultValue = $nonReferenceableProperties->protectedArrayPropertyWithoutDefaultValue;
isset($nonReferenceableProperties->protectedIterablePropertyWithoutDefaultValue) && $this->protectedIterablePropertyWithoutDefaultValue = $nonReferenceableProperties->protectedIterablePropertyWithoutDefaultValue;
isset($nonReferenceableProperties->protectedObjectProperty) && $this->protectedObjectProperty = $nonReferenceableProperties->protectedObjectProperty;
isset($nonReferenceableProperties->protectedClassProperty) && $this->protectedClassProperty = $nonReferenceableProperties->protectedClassProperty;
static $cacheAssignProxyManagerTestAsset_ClassWithMixedTypedProperties;

$cacheAssignProxyManagerTestAsset_ClassWithMixedTypedProperties ?? $cacheAssignProxyManagerTestAsset_ClassWithMixedTypedProperties = \Closure::bind(function ($instance, $nonReferenceableProperties) {
    isset($nonReferenceableProperties->privateBoolPropertyWithoutDefaultValue_on_ProxyManagerTestAsset_ClassWithMixedTypedProperties) && $this->privateBoolPropertyWithoutDefaultValue = $nonReferenceableProperties->privateBoolPropertyWithoutDefaultValue_on_ProxyManagerTestAsset_ClassWithMixedTypedProperties;
    isset($nonReferenceableProperties->privateIntPropertyWithoutDefaultValue_on_ProxyManagerTestAsset_ClassWithMixedTypedProperties) && $this->privateIntPropertyWithoutDefaultValue = $nonReferenceableProperties->privateIntPropertyWithoutDefaultValue_on_ProxyManagerTestAsset_ClassWithMixedTypedProperties;
    isset($nonReferenceableProperties->privateFloatPropertyWithoutDefaultValue_on_ProxyManagerTestAsset_ClassWithMixedTypedProperties) && $this->privateFloatPropertyWithoutDefaultValue = $nonReferenceableProperties->privateFloatPropertyWithoutDefaultValue_on_ProxyManagerTestAsset_ClassWithMixedTypedProperties;
    isset($nonReferenceableProperties->privateStringPropertyWithoutDefaultValue_on_ProxyManagerTestAsset_ClassWithMixedTypedProperties) && $this->privateStringPropertyWithoutDefaultValue = $nonReferenceableProperties->privateStringPropertyWithoutDefaultValue_on_ProxyManagerTestAsset_ClassWithMixedTypedProperties;
    isset($nonReferenceableProperties->privateArrayPropertyWithoutDefaultValue_on_ProxyManagerTestAsset_ClassWithMixedTypedProperties) && $this->privateArrayPropertyWithoutDefaultValue = $nonReferenceableProperties->privateArrayPropertyWithoutDefaultValue_on_ProxyManagerTestAsset_ClassWithMixedTypedProperties;
    isset($nonReferenceableProperties->privateIterablePropertyWithoutDefaultValue_on_ProxyManagerTestAsset_ClassWithMixedTypedProperties) && $this->privateIterablePropertyWithoutDefaultValue = $nonReferenceableProperties->privateIterablePropertyWithoutDefaultValue_on_ProxyManagerTestAsset_ClassWithMixedTypedProperties;
    isset($nonReferenceableProperties->privateObjectProperty_on_ProxyManagerTestAsset_ClassWithMixedTypedProperties) && $this->privateObjectProperty = $nonReferenceableProperties->privateObjectProperty_on_ProxyManagerTestAsset_ClassWithMixedTypedProperties;
    isset($nonReferenceableProperties->privateClassProperty_on_ProxyManagerTestAsset_ClassWithMixedTypedProperties) && $this->privateClassProperty = $nonReferenceableProperties->privateClassProperty_on_ProxyManagerTestAsset_ClassWithMixedTypedProperties;
}, null, 'ProxyManagerTestAsset\\ClassWithMixedTypedProperties');

$cacheAssignProxyManagerTestAsset_ClassWithMixedTypedProperties($this, $nonReferenceableProperties);
$this->track = false;

return $result;
PHP;

        self::assertSame(
            $expectedCode,
            $callInitializer->getBody()
        );
    }

    /**
     * @requires PHP 8.1
     */
    public function testBodyStructureWithReadOnlyProperties(): void
    {
        $initializer           = $this->createMock(PropertyGenerator::class);
        $initializationTracker = $this->createMock(PropertyGenerator::class);

        $initializer->method('getName')->willReturn('init');
        $initializationTracker->method('getName')->willReturn('track');

        $callInitializer = new CallInitializer(
            $initializer,
            $initializationTracker,
            Properties::fromReflectionClass(new ReflectionClass(ClassWithReadOnlyProperties::class))
        );

        $expectedCode = <<<'PHP'
if ($this->track || ! $this->init) {
    return;
}

$this->track = true;




$nonReferenceableProperties = new class() {
    public null|\stdClass|string $property0;
    public ?string $property1;
    public ?string $property2_on_ProxyManagerTestAsset_ClassWithReadOnlyProperties;
};
$properties = [
    'property0' => & $nonReferenceableProperties->property0,
    '' . "\0" . '*' . "\0" . 'property1' => & $nonReferenceableProperties->property1,
    '' . "\0" . 'ProxyManagerTestAsset\\ClassWithReadOnlyProperties' . "\0" . 'property2' => & $nonReferenceableProperties->property2_on_ProxyManagerTestAsset_ClassWithReadOnlyProperties,
];



$result = $this->init->__invoke($this, $methodName, $parameters, $this->init, $properties);
static $cacheAssignProxyManagerTestAsset_ClassWithReadOnlyProperties;

$cacheAssignProxyManagerTestAsset_ClassWithReadOnlyProperties ?? $cacheAssignProxyManagerTestAsset_ClassWithReadOnlyProperties = \Closure::bind(function ($instance, $nonReferenceableProperties) {
    isset($nonReferenceableProperties->property0) && $this->property0 = $nonReferenceableProperties->property0;
    isset($nonReferenceableProperties->property1) && $this->property1 = $nonReferenceableProperties->property1;
    isset($nonReferenceableProperties->property2_on_ProxyManagerTestAsset_ClassWithReadOnlyProperties) && $this->property2 = $nonReferenceableProperties->property2_on_ProxyManagerTestAsset_ClassWithReadOnlyProperties;
}, null, 'ProxyManagerTestAsset\\ClassWithReadOnlyProperties');

$cacheAssignProxyManagerTestAsset_ClassWithReadOnlyProperties($this, $nonReferenceableProperties);
$this->track = false;

return $result;
PHP;

        self::assertSame(
            $expectedCode,
            $callInitializer->getBody()
        );
    }
}
