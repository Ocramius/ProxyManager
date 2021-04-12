<?php

declare(strict_types=1);

namespace ProxyManagerTest\ProxyGenerator\AccessInterceptorScopeLocalizer\MethodGenerator;

use Laminas\Code\Generator\PropertyGenerator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ProxyManager\ProxyGenerator\AccessInterceptorScopeLocalizer\MethodGenerator\BindProxyProperties;
use ProxyManagerTestAsset\ClassWithMixedProperties;
use ProxyManagerTestAsset\ClassWithMixedReferenceableTypedProperties;
use ProxyManagerTestAsset\ClassWithPrivateProperties;
use ProxyManagerTestAsset\ClassWithProtectedProperties;
use ReflectionClass;

/**
 * Tests for {@see \ProxyManager\ProxyGenerator\AccessInterceptorScopeLocalizer\MethodGenerator\BindProxyProperties}
 *
 * @covers \ProxyManager\ProxyGenerator\AccessInterceptorScopeLocalizer\MethodGenerator\BindProxyProperties
 * @group Coverage
 */
final class BindProxyPropertiesTest extends TestCase
{
    /** @var PropertyGenerator&MockObject */
    private PropertyGenerator $prefixInterceptors;

    /** @var PropertyGenerator&MockObject */
    private PropertyGenerator $suffixInterceptors;

    protected function setUp(): void
    {
        $this->prefixInterceptors = $this->createMock(PropertyGenerator::class);
        $this->suffixInterceptors = $this->createMock(PropertyGenerator::class);

        $this->prefixInterceptors->method('getName')->willReturn('pre');
        $this->suffixInterceptors->method('getName')->willReturn('post');
    }

    public function testSignature(): void
    {
        $method = new BindProxyProperties(
            new ReflectionClass(ClassWithProtectedProperties::class),
            $this->prefixInterceptors,
            $this->suffixInterceptors
        );
        self::assertSame('bindProxyProperties', $method->getName());
        self::assertSame('private', $method->getVisibility());
        self::assertFalse($method->isStatic());

        $parameters = $method->getParameters();

        self::assertCount(3, $parameters);

        self::assertSame(
            ClassWithProtectedProperties::class,
            $parameters['localizedObject']->getType()
        );
        self::assertSame('array', $parameters['prefixInterceptors']->getType());
        self::assertSame('array', $parameters['suffixInterceptors']->getType());
    }

    public function testBodyStructure(): void
    {
        $method = new BindProxyProperties(
            new ReflectionClass(ClassWithMixedProperties::class),
            $this->prefixInterceptors,
            $this->suffixInterceptors
        );

        $expectedCode =

        $expectedCode = <<<'PHP'
$class = new \ReflectionObject($localizedObject);
$this->bindProxyProperty($localizedObject, $class, 'publicProperty0');
$this->bindProxyProperty($localizedObject, $class, 'publicProperty1');
$this->bindProxyProperty($localizedObject, $class, 'publicProperty2');
$this->bindProxyProperty($localizedObject, $class, 'protectedProperty0');
$this->bindProxyProperty($localizedObject, $class, 'protectedProperty1');
$this->bindProxyProperty($localizedObject, $class, 'protectedProperty2');
$this->bindProxyProperty($localizedObject, $class, 'privateProperty0');
$this->bindProxyProperty($localizedObject, $class, 'privateProperty1');
$this->bindProxyProperty($localizedObject, $class, 'privateProperty2');
$this->pre = $prefixInterceptors;
$this->post = $suffixInterceptors;
PHP;

        self::assertSame($expectedCode, $method->getBody());
    }

    public function testBodyStructureWithProtectedProperties(): void
    {
        $method = new BindProxyProperties(
            new ReflectionClass(ClassWithProtectedProperties::class),
            $this->prefixInterceptors,
            $this->suffixInterceptors
        );

        $expectedCode = <<<'PHP'
$class = new \ReflectionObject($localizedObject);
$this->bindProxyProperty($localizedObject, $class, 'property0');
$this->bindProxyProperty($localizedObject, $class, 'property1');
$this->bindProxyProperty($localizedObject, $class, 'property2');
$this->bindProxyProperty($localizedObject, $class, 'property3');
$this->bindProxyProperty($localizedObject, $class, 'property4');
$this->bindProxyProperty($localizedObject, $class, 'property5');
$this->bindProxyProperty($localizedObject, $class, 'property6');
$this->bindProxyProperty($localizedObject, $class, 'property7');
$this->bindProxyProperty($localizedObject, $class, 'property8');
$this->bindProxyProperty($localizedObject, $class, 'property9');
$this->pre = $prefixInterceptors;
$this->post = $suffixInterceptors;
PHP;

        self::assertSame(
            $expectedCode,
            $method->getBody()
        );
    }

    public function testBodyStructureWithPrivateProperties(): void
    {
        $method = new BindProxyProperties(
            new ReflectionClass(ClassWithPrivateProperties::class),
            $this->prefixInterceptors,
            $this->suffixInterceptors
        );

        $expectedCode = <<<'PHP'
$class = new \ReflectionObject($localizedObject);
$this->bindProxyProperty($localizedObject, $class, 'property0');
$this->bindProxyProperty($localizedObject, $class, 'property1');
$this->bindProxyProperty($localizedObject, $class, 'property2');
$this->bindProxyProperty($localizedObject, $class, 'property3');
$this->bindProxyProperty($localizedObject, $class, 'property4');
$this->bindProxyProperty($localizedObject, $class, 'property5');
$this->bindProxyProperty($localizedObject, $class, 'property6');
$this->bindProxyProperty($localizedObject, $class, 'property7');
$this->bindProxyProperty($localizedObject, $class, 'property8');
$this->bindProxyProperty($localizedObject, $class, 'property9');
$this->pre = $prefixInterceptors;
$this->post = $suffixInterceptors;
PHP;


        self::assertSame(
            $expectedCode,
            $method->getBody()
        );
    }

    public function testBodyStructureWithTypedProperties(): void
    {
        $method = new BindProxyProperties(
            new ReflectionClass(ClassWithMixedReferenceableTypedProperties::class),
            $this->prefixInterceptors,
            $this->suffixInterceptors
        );

        $expectedCode = <<<'PHP'
$class = new \ReflectionObject($localizedObject);
$this->bindProxyProperty($localizedObject, $class, 'publicUnTypedProperty');
$this->bindProxyProperty($localizedObject, $class, 'publicBoolProperty');
$this->bindProxyProperty($localizedObject, $class, 'publicNullableBoolProperty');
$this->bindProxyProperty($localizedObject, $class, 'publicIntProperty');
$this->bindProxyProperty($localizedObject, $class, 'publicNullableIntProperty');
$this->bindProxyProperty($localizedObject, $class, 'publicFloatProperty');
$this->bindProxyProperty($localizedObject, $class, 'publicNullableFloatProperty');
$this->bindProxyProperty($localizedObject, $class, 'publicStringProperty');
$this->bindProxyProperty($localizedObject, $class, 'publicNullableStringProperty');
$this->bindProxyProperty($localizedObject, $class, 'publicArrayProperty');
$this->bindProxyProperty($localizedObject, $class, 'publicNullableArrayProperty');
$this->bindProxyProperty($localizedObject, $class, 'publicIterableProperty');
$this->bindProxyProperty($localizedObject, $class, 'publicNullableIterableProperty');
$this->bindProxyProperty($localizedObject, $class, 'protectedUnTypedProperty');
$this->bindProxyProperty($localizedObject, $class, 'protectedBoolProperty');
$this->bindProxyProperty($localizedObject, $class, 'protectedNullableBoolProperty');
$this->bindProxyProperty($localizedObject, $class, 'protectedIntProperty');
$this->bindProxyProperty($localizedObject, $class, 'protectedNullableIntProperty');
$this->bindProxyProperty($localizedObject, $class, 'protectedFloatProperty');
$this->bindProxyProperty($localizedObject, $class, 'protectedNullableFloatProperty');
$this->bindProxyProperty($localizedObject, $class, 'protectedStringProperty');
$this->bindProxyProperty($localizedObject, $class, 'protectedNullableStringProperty');
$this->bindProxyProperty($localizedObject, $class, 'protectedArrayProperty');
$this->bindProxyProperty($localizedObject, $class, 'protectedNullableArrayProperty');
$this->bindProxyProperty($localizedObject, $class, 'protectedIterableProperty');
$this->bindProxyProperty($localizedObject, $class, 'protectedNullableIterableProperty');
$this->bindProxyProperty($localizedObject, $class, 'privateUnTypedProperty');
$this->bindProxyProperty($localizedObject, $class, 'privateBoolProperty');
$this->bindProxyProperty($localizedObject, $class, 'privateNullableBoolProperty');
$this->bindProxyProperty($localizedObject, $class, 'privateIntProperty');
$this->bindProxyProperty($localizedObject, $class, 'privateNullableIntProperty');
$this->bindProxyProperty($localizedObject, $class, 'privateFloatProperty');
$this->bindProxyProperty($localizedObject, $class, 'privateNullableFloatProperty');
$this->bindProxyProperty($localizedObject, $class, 'privateStringProperty');
$this->bindProxyProperty($localizedObject, $class, 'privateNullableStringProperty');
$this->bindProxyProperty($localizedObject, $class, 'privateArrayProperty');
$this->bindProxyProperty($localizedObject, $class, 'privateNullableArrayProperty');
$this->bindProxyProperty($localizedObject, $class, 'privateIterableProperty');
$this->bindProxyProperty($localizedObject, $class, 'privateNullableIterableProperty');
$this->pre = $prefixInterceptors;
$this->post = $suffixInterceptors;
PHP;

        self::assertSame(
            $expectedCode,
            $method->getBody()
        );
    }
}
