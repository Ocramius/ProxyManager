<?php

declare(strict_types=1);

namespace ProxyManagerTest\Functional;

use Error;
use PHPUnit\Framework\TestCase;
use ProxyManager\Factory\NullObjectFactory;
use ProxyManager\Proxy\NullObjectInterface;
use ProxyManagerTestAsset\BaseClass;
use ProxyManagerTestAsset\BaseInterface;
use ProxyManagerTestAsset\ClassWithMethodWithByRefVariadicFunction;
use ProxyManagerTestAsset\ClassWithMethodWithVariadicFunction;
use ProxyManagerTestAsset\ClassWithParentHint;
use ProxyManagerTestAsset\ClassWithPublicStringNullableNullDefaultTypedProperty;
use ProxyManagerTestAsset\ClassWithPublicStringNullableTypedProperty;
use ProxyManagerTestAsset\ClassWithPublicStringTypedProperty;
use ProxyManagerTestAsset\ClassWithSelfHint;
use ProxyManagerTestAsset\EmptyClass;
use ProxyManagerTestAsset\VoidCounter;
use ReflectionProperty;
use stdClass;

use function array_values;
use function assert;
use function random_int;
use function serialize;
use function uniqid;
use function unserialize;

/**
 * Tests for {@see \ProxyManager\ProxyGenerator\NullObjectGenerator} produced objects
 *
 * @group Functional
 * @coversNothing
 */
final class NullObjectFunctionalTest extends TestCase
{
    /**
     * @param mixed[] $params
     * @psalm-param class-string $className
     *
     * @dataProvider getProxyMethods
     */
    public function testMethodCalls(string $className, string $method, array $params): void
    {
        $proxy = (new NullObjectFactory())->createProxy($className);

        $this->assertNullMethodCall($proxy, $method, $params);
    }

    /**
     * @param mixed[] $params
     * @psalm-param class-string $className
     *
     * @dataProvider getProxyMethods
     */
    public function testMethodCallsAfterUnSerialization(string $className, string $method, array $params): void
    {
        $proxy = unserialize(serialize((new NullObjectFactory())->createProxy($className)));
        assert($proxy instanceof NullObjectInterface);

        $this->assertNullMethodCall($proxy, $method, $params);
    }

    /**
     * @param mixed[] $params
     * @psalm-param class-string $className
     *
     * @dataProvider getProxyMethods
     */
    public function testMethodCallsAfterCloning(string $className, string $method, array $params): void
    {
        $proxy = (new NullObjectFactory())->createProxy($className);

        $this->assertNullMethodCall(clone $proxy, $method, $params);
    }

    /**
     * @dataProvider getPropertyAccessProxies
     */
    public function testPropertyReadAccess(NullObjectInterface $proxy, string $publicProperty): void
    {
        if (! $this->propertyHasDefaultNullableValue(new ReflectionProperty($proxy, $publicProperty))) {
            // Accessing a typed property without default value before initialization
            $this->expectException(Error::class);
            $this->expectExceptionMessageMatches('/must not be accessed before initialization/');
        }

        self::assertNull($proxy->$publicProperty);
    }

    /**
     * @dataProvider getPropertyAccessProxies
     */
    public function testPropertyWriteAccess(NullObjectInterface $proxy, string $publicProperty): void
    {
        $newValue               = uniqid('', true);
        $proxy->$publicProperty = $newValue;

        self::assertSame($newValue, $proxy->$publicProperty);
    }

    /**
     * @dataProvider getPropertyAccessProxies
     */
    public function testPropertyExistence(NullObjectInterface $proxy, string $publicProperty): void
    {
        if (! $this->propertyHasDefaultNullableValue(new ReflectionProperty($proxy, $publicProperty))) {
            // Accessing a typed property without default value before initialization
            $this->expectException(Error::class);
            $this->expectExceptionMessageMatches('/must not be accessed before initialization/');
        }

        self::assertNull($proxy->$publicProperty);
    }

    /**
     * @dataProvider getPropertyAccessProxies
     */
    public function testPropertyUnset(NullObjectInterface $proxy, string $publicProperty): void
    {
        unset($proxy->$publicProperty);

        self::assertFalse(isset($proxy->$publicProperty));
    }

    /**
     * Generates a list of object | invoked method | parameters | expected result
     *
     * @return string[][]|null[][]|mixed[][][]|object[][]
     */
    public function getProxyMethods(): array
    {
        $selfHintParam = new ClassWithSelfHint();
        $empty         = new EmptyClass();

        return [
            [
                BaseClass::class,
                'publicMethod',
                [],
                'publicMethodDefault',
            ],
            [
                BaseClass::class,
                'publicTypeHintedMethod',
                ['param' => new stdClass()],
                'publicTypeHintedMethodDefault',
            ],
            [
                BaseClass::class,
                'publicByReferenceMethod',
                [],
                'publicByReferenceMethodDefault',
            ],
            [
                BaseInterface::class,
                'publicMethod',
                [],
                'publicMethodDefault',
            ],
            [
                ClassWithSelfHint::class,
                'selfHintMethod',
                ['parameter' => $selfHintParam],
                $selfHintParam,
            ],
            [
                ClassWithParentHint::class,
                'parentHintMethod',
                ['parameter' => $empty],
                $empty,
            ],
            [
                ClassWithMethodWithVariadicFunction::class,
                'buz',
                ['Ocramius', 'Malukenho'],
                null,
            ],
            [
                ClassWithMethodWithByRefVariadicFunction::class,
                'tuz',
                ['Ocramius', 'Malukenho'],
                null,
            ],
            [
                VoidCounter::class,
                'increment',
                [random_int(10, 1000)],
                null,
            ],
        ];
    }

    /**
     * Generates proxies and instances with a public property to feed to the property accessor methods
     *
     * @return array<int, array<int, NullObjectInterface|string|null>>
     */
    public function getPropertyAccessProxies(): array
    {
        $factory    = new NullObjectFactory();
        $serialized = unserialize(serialize($factory->createProxy(BaseClass::class)));
        assert($serialized instanceof NullObjectInterface);

        return [
            [
                $factory->createProxy(BaseClass::class),
                'publicProperty',
                'publicPropertyDefault',
            ],
            [
                $serialized,
                'publicProperty',
                'publicPropertyDefault',
            ],
            [
                $factory->createProxy(ClassWithPublicStringTypedProperty::class),
                'typedProperty',
                'Typed property initialized value',
            ],
            [
                $factory->createProxy(ClassWithPublicStringNullableTypedProperty::class),
                'typedNullableProperty',
                'Typed nullable property initialized value',
            ],
            [
                $factory->createProxy(ClassWithPublicStringNullableNullDefaultTypedProperty::class),
                'typedNullableNullDefaultProperty',
                null,
            ],
        ];
    }

    /**
     * @param mixed[] $parameters
     */
    private function assertNullMethodCall(NullObjectInterface $proxy, string $methodName, array $parameters): void
    {
        $method = [$proxy, $methodName];

        self::assertIsCallable($method);

        $parameterValues = array_values($parameters);

        self::assertNull($method(...$parameterValues));
    }

    private function propertyHasDefaultNullableValue(ReflectionProperty $property): bool
    {
        $type = $property->getType();

        return $type === null
            || $type->allowsNull();
    }
}
