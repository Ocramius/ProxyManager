<?php

declare(strict_types=1);

namespace ProxyManagerTest\Functional;

use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use ProxyManager\Configuration;
use ProxyManager\Factory\AccessInterceptorScopeLocalizerFactory;
use ProxyManager\Proxy\AccessInterceptorInterface;
use ProxyManager\ProxyGenerator\Util\Properties;
use ProxyManagerTest\Assert;
use ProxyManagerTestAsset\BaseClass;
use ProxyManagerTestAsset\CallableInterface;
use ProxyManagerTestAsset\ClassWithCounterConstructor;
use ProxyManagerTestAsset\ClassWithDynamicArgumentsMethod;
use ProxyManagerTestAsset\ClassWithMethodWithByRefVariadicFunction;
use ProxyManagerTestAsset\ClassWithMethodWithVariadicFunction;
use ProxyManagerTestAsset\ClassWithParentHint;
use ProxyManagerTestAsset\ClassWithPublicArrayPropertyAccessibleViaMethod;
use ProxyManagerTestAsset\ClassWithPublicProperties;
use ProxyManagerTestAsset\ClassWithSelfHint;
use ProxyManagerTestAsset\EmptyClass;
use ProxyManagerTestAsset\VoidCounter;
use ReflectionClass;
use stdClass;
use function array_values;
use function get_class;
use function random_int;
use function serialize;
use function uniqid;
use function unserialize;

/**
 * Tests for {@see \ProxyManager\ProxyGenerator\AccessInterceptorScopeLocalizerGenerator} produced objects
 *
 * @group Functional
 * @coversNothing
 */
final class AccessInterceptorScopeLocalizerFunctionalTest extends TestCase
{
    /**
     * @param mixed[] $params
     * @param mixed   $expectedValue
     *
     * @dataProvider getProxyMethods
     */
    public function testMethodCalls(object $instance, string $method, array $params, $expectedValue) : void
    {
        $proxy = (new AccessInterceptorScopeLocalizerFactory())->createProxy($instance);

        $this->assertProxySynchronized($instance, $proxy);

        $callback = [$proxy, $method];

        self::assertIsCallable($callback);
        self::assertSame($expectedValue, $callback(...array_values($params)));

        $listener = $this->createMock(CallableInterface::class);
        $listener
            ->expects(self::once())
            ->method('__invoke')
            ->with($proxy, $proxy, $method, $params, false);

        $proxy->setMethodPrefixInterceptor(
            $method,
            static function (
                AccessInterceptorInterface $proxy,
                object $instance,
                string $method,
                array $params,
                bool & $returnEarly
            ) use ($listener) : void {
                $listener->__invoke($proxy, $instance, $method, $params, $returnEarly);
            }
        );

        self::assertSame($expectedValue, $callback(...array_values($params)));

        $random = uniqid('', true);

        $proxy->setMethodPrefixInterceptor(
            $method,
            static function (
                AccessInterceptorInterface $proxy,
                object $instance,
                string $method,
                array $params,
                bool & $returnEarly
            ) use ($random) : string {
                $returnEarly = true;

                return $random;
            }
        );

        self::assertSame($random, $callback(...array_values($params)));

        $this->assertProxySynchronized($instance, $proxy);
    }

    /**
     * @param mixed[] $params
     * @param mixed   $expectedValue
     *
     * @dataProvider getProxyMethods
     */
    public function testMethodCallsWithSuffixListener(
        object $instance,
        string $method,
        array $params,
        $expectedValue
    ) : void {
        $proxy    = (new AccessInterceptorScopeLocalizerFactory())->createProxy($instance);
        $callback = [$proxy, $method];

        self::assertIsCallable($callback);

        $listener = $this->createMock(CallableInterface::class);
        $listener
            ->expects(self::once())
            ->method('__invoke')
            ->with($proxy, $proxy, $method, $params, $expectedValue, false);

        $proxy->setMethodSuffixInterceptor(
            $method,
            /** @param mixed $returnValue */
            static function (
                AccessInterceptorInterface $proxy,
                object $instance,
                string $method,
                array $params,
                $returnValue,
                bool & $returnEarly
            ) use ($listener) : void {
                $listener->__invoke($proxy, $instance, $method, $params, $returnValue, $returnEarly);
            }
        );

        self::assertSame($expectedValue, $callback(...array_values($params)));

        $random = uniqid('', true);

        $proxy->setMethodSuffixInterceptor(
            $method,
            /** @param mixed $returnValue */
            static function (
                AccessInterceptorInterface $proxy,
                object $instance,
                string $method,
                array $params,
                $returnValue,
                bool & $returnEarly
            ) use ($random) : string {
                $returnEarly = true;

                return $random;
            }
        );

        self::assertSame($random, $callback(...array_values($params)));

        $this->assertProxySynchronized($instance, $proxy);
    }

    /**
     * @param mixed[] $params
     * @param mixed   $expectedValue
     *
     * @dataProvider getProxyMethods
     */
    public function testMethodCallsAfterUnSerialization(
        object $instance,
        string $method,
        array $params,
        $expectedValue
    ) : void {
        /** @var AccessInterceptorInterface $proxy */
        $proxy = unserialize(serialize((new AccessInterceptorScopeLocalizerFactory())->createProxy($instance)));

        $callback = [$proxy, $method];

        self::assertIsCallable($callback);
        self::assertSame($expectedValue, $callback(...array_values($params)));
        $this->assertProxySynchronized($instance, $proxy);
    }

    /**
     * @param mixed[] $params
     * @param mixed   $expectedValue
     *
     * @dataProvider getProxyMethods
     */
    public function testMethodCallsAfterCloning(
        object $instance,
        string $method,
        array $params,
        $expectedValue
    ) : void {
        $proxy    = (new AccessInterceptorScopeLocalizerFactory())->createProxy($instance);
        $cloned   = clone $proxy;
        $callback = [$cloned, $method];

        $this->assertProxySynchronized($instance, $proxy);
        self::assertIsCallable($callback);
        self::assertSame($expectedValue, $callback(...array_values($params)));
        $this->assertProxySynchronized($instance, $proxy);
    }

    /**
     * @param mixed $propertyValue
     *
     * @dataProvider getPropertyAccessProxies
     */
    public function testPropertyReadAccess(
        object $instance,
        AccessInterceptorInterface $proxy,
        string $publicProperty,
        $propertyValue
    ) : void {
        self::assertSame($propertyValue, $proxy->$publicProperty);
        $this->assertProxySynchronized($instance, $proxy);
    }

    /**
     * @dataProvider getPropertyAccessProxies
     */
    public function testPropertyWriteAccess(object $instance, AccessInterceptorInterface $proxy, string $publicProperty) : void
    {
        $newValue               = uniqid('value', true);
        $proxy->$publicProperty = $newValue;

        self::assertSame($newValue, $proxy->$publicProperty);
        $this->assertProxySynchronized($instance, $proxy);
    }

    /**
     * @dataProvider getPropertyAccessProxies
     */
    public function testPropertyExistence(object $instance, AccessInterceptorInterface $proxy, string $publicProperty) : void
    {
        self::assertSame(isset($instance->$publicProperty), isset($proxy->$publicProperty));
        $this->assertProxySynchronized($instance, $proxy);

        $instance->$publicProperty = null;
        self::assertFalse(isset($proxy->$publicProperty));
        $this->assertProxySynchronized($instance, $proxy);
    }

    /**
     * @dataProvider getPropertyAccessProxies
     */
    public function testPropertyUnset(object $instance, AccessInterceptorInterface $proxy, string $publicProperty) : void
    {
        self::markTestSkipped('It is currently not possible to synchronize properties un-setting');
        unset($proxy->$publicProperty);

        self::assertFalse(isset($instance->$publicProperty));
        self::assertFalse(isset($proxy->$publicProperty));
        $this->assertProxySynchronized($instance, $proxy);
    }

    /**
     * Verifies that accessing a public property containing an array behaves like in a normal context
     */
    public function testCanWriteToArrayKeysInPublicProperty() : void
    {
        $instance = new ClassWithPublicArrayPropertyAccessibleViaMethod();
        $proxy    = (new AccessInterceptorScopeLocalizerFactory())->createProxy($instance);

        $proxy->arrayProperty['foo'] = 'bar';

        self::assertSame('bar', $proxy->getArrayProperty()['foo']);

        $proxy->arrayProperty = ['tab' => 'taz'];

        self::assertSame(['tab' => 'taz'], $proxy->arrayProperty);

        $this->assertProxySynchronized($instance, $proxy);
    }

    /**
     * Verifies that public properties retrieved via `__get` don't get modified in the object state
     */
    public function testWillNotModifyRetrievedPublicProperties() : void
    {
        $instance = new ClassWithPublicProperties();
        $proxy    = (new AccessInterceptorScopeLocalizerFactory())->createProxy($instance);

        $variable = $proxy->property0;

        self::assertByRefVariableValueSame('property0', $variable);

        $variable = 'foo';

        self::assertSame('property0', $proxy->property0);

        $this->assertProxySynchronized($instance, $proxy);

        self::assertByRefVariableValueSame('foo', $variable);
    }

    /**
     * Verifies that public properties references retrieved via `__get` modify in the object state
     */
    public function testWillModifyByRefRetrievedPublicProperties() : void
    {
        $instance = new ClassWithPublicProperties();
        $proxy    = (new AccessInterceptorScopeLocalizerFactory())->createProxy($instance);

        $variable = & $proxy->property0;

        self::assertByRefVariableValueSame('property0', $variable);

        $variable = 'foo';

        self::assertSame('foo', $proxy->property0);

        $this->assertProxySynchronized($instance, $proxy);

        self::assertByRefVariableValueSame('foo', $variable);
    }

    /**
     * @group 115
     * @group 175
     */
    public function testWillBehaveLikeObjectWithNormalConstructor() : void
    {
        $instance = new ClassWithCounterConstructor(10);

        self::assertSame(10, $instance->amount, 'Verifying that test asset works as expected');
        self::assertSame(10, $instance->getAmount(), 'Verifying that test asset works as expected');
        $instance->__construct(3);
        self::assertSame(13, $instance->amount, 'Verifying that test asset works as expected');
        self::assertSame(13, $instance->getAmount(), 'Verifying that test asset works as expected');

        $proxyName = get_class(
            (new AccessInterceptorScopeLocalizerFactory())
                ->createProxy(new ClassWithCounterConstructor(0))
        );

        $proxy = new $proxyName(15);

        self::assertSame(15, $proxy->amount, 'Verifying that the proxy constructor works as expected');
        self::assertSame(15, $proxy->getAmount(), 'Verifying that the proxy constructor works as expected');
        $proxy->__construct(5);
        self::assertSame(20, $proxy->amount, 'Verifying that the proxy constructor works as expected');
        self::assertSame(20, $proxy->getAmount(), 'Verifying that the proxy constructor works as expected');
    }

    /**
     * Generates a list of object | invoked method | parameters | expected result
     *
     * @return array<int, array<object|array<string, mixed>|string>>
     */
    public static function getProxyMethods() : array
    {
        $selfHintParam = new ClassWithSelfHint();
        $empty         = new EmptyClass();

        return [
            [
                new BaseClass(),
                'publicMethod',
                [],
                'publicMethodDefault',
            ],
            [
                new BaseClass(),
                'publicTypeHintedMethod',
                ['param' => new stdClass()],
                'publicTypeHintedMethodDefault',
            ],
            [
                new BaseClass(),
                'publicByReferenceMethod',
                [],
                'publicByReferenceMethodDefault',
            ],
            [
                new ClassWithSelfHint(),
                'selfHintMethod',
                ['parameter' => $selfHintParam],
                $selfHintParam,
            ],
            [
                new ClassWithParentHint(),
                'parentHintMethod',
                ['parameter' => $empty],
                $empty,
            ],
        ];
    }

    /**
     * Generates proxies and instances with a public property to feed to the property accessor methods
     *
     * @return array<int, array<int, object|AccessInterceptorInterface|string>>
     */
    public function getPropertyAccessProxies() : array
    {
        $instance = new BaseClass();

        return [
            [
                $instance,
                (new AccessInterceptorScopeLocalizerFactory())->createProxy($instance),
                'publicProperty',
                'publicPropertyDefault',
            ],
        ];
    }

    private function assertProxySynchronized(object $instance, AccessInterceptorInterface $proxy) : void
    {
        $reflectionClass = new ReflectionClass($instance);

        foreach (Properties::fromReflectionClass($reflectionClass)->getInstanceProperties() as $property) {
            $property->setAccessible(true);

            self::assertSame(
                $property->getValue($instance),
                $property->getValue($proxy),
                'Property "' . $property->getName() . '" is synchronized between instance and proxy'
            );
        }
    }

    public function testWillForwardVariadicArguments() : void
    {
        $configuration = new Configuration();
        $factory       = new AccessInterceptorScopeLocalizerFactory($configuration);
        $targetObject  = new ClassWithMethodWithVariadicFunction();

        $object = $factory->createProxy(
            $targetObject,
            [
                'bar' => static function () : string {
                    return 'Foo Baz';
                },
            ]
        );

        self::assertNull($object->bar);
        self::assertNull($object->baz);

        $object->foo('Ocramius', 'Malukenho', 'Danizord');
        self::assertSame('Ocramius', $object->bar);
        self::assertSame(['Malukenho', 'Danizord'], Assert::readAttribute($object, 'baz'));
    }

    /**
     * @group 265
     */
    public function testWillForwardVariadicByRefArguments() : void
    {
        $configuration = new Configuration();
        $factory       = new AccessInterceptorScopeLocalizerFactory($configuration);
        $targetObject  = new ClassWithMethodWithByRefVariadicFunction();

        $object = $factory->createProxy(
            $targetObject,
            [
                'bar' => static function () : string {
                    return 'Foo Baz';
                },
            ]
        );

        $parameters = ['a', 'b', 'c'];

        // first, testing normal variadic behavior (verifying we didn't screw up in the test asset)
        self::assertSame(['a', 'changed', 'c'], (new ClassWithMethodWithByRefVariadicFunction())->tuz(...$parameters));
        self::assertSame(['a', 'changed', 'c'], $object->tuz(...$parameters));
        self::assertSame(['a', 'changed', 'c'], $parameters, 'by-ref variadic parameter was changed');
    }

    /**
     * This test documents a known limitation: `func_get_args()` (and similar) don't work in proxied APIs.
     * If you manage to make this test pass, then please do send a patch
     *
     * @group 265
     */
    public function testWillNotForwardDynamicArguments() : void
    {
        $object = (new AccessInterceptorScopeLocalizerFactory())
            ->createProxy(
                new ClassWithDynamicArgumentsMethod(),
                [
                    'dynamicArgumentsMethod' => static function () : string {
                        return 'Foo Baz';
                    },
                ]
            );

        self::assertSame(['a', 'b'], (new ClassWithDynamicArgumentsMethod())->dynamicArgumentsMethod('a', 'b'));

        $this->expectException(ExpectationFailedException::class);

        self::assertSame(['a', 'b'], $object->dynamicArgumentsMethod('a', 'b'));
    }

    /**
     * @group 327
     */
    public function testWillInterceptAndReturnEarlyOnVoidMethod() : void
    {
        $skip      = random_int(100, 200);
        $addMore   = random_int(201, 300);
        $increment = random_int(301, 400);

        $object = (new AccessInterceptorScopeLocalizerFactory())
            ->createProxy(
                new VoidCounter(),
                [
                    'increment' => static function (
                        AccessInterceptorInterface $proxy,
                        VoidCounter $instance,
                        string $method,
                        array $params,
                        ?bool & $returnEarly
                    ) use ($skip) : void {
                        if ($skip !== $params['amount']) {
                            return;
                        }

                        $returnEarly = true;
                    },
                ],
                [
                    'increment' => static function (
                        AccessInterceptorInterface $proxy,
                        VoidCounter $instance,
                        string $method,
                        array $params,
                        ?bool & $returnEarly
                    ) use ($addMore) : void {
                        if ($addMore !== $params['amount']) {
                            return;
                        }

                        $instance->counter += 1;
                    },
                ]
            );

        $object->increment($skip);
        self::assertSame(0, $object->counter);

        $object->increment($increment);
        self::assertSame($increment, $object->counter);

        $object->increment($addMore);
        self::assertSame($increment + $addMore + 1, $object->counter);
    }

    /**
     * @param mixed $expected
     * @param mixed $actual
     *
     * @psalm-template ExpectedType
     * @psalm-param ExpectedType $expected
     * @psalm-assert ExpectedType $actual
     */
    private static function assertByRefVariableValueSame($expected, & $actual) : void
    {
        self::assertSame($expected, $actual);
    }
}
