<?php

declare(strict_types=1);

namespace ProxyManagerTest\Functional;

use BadMethodCallException;
use Closure;
use PHPUnit\Framework\MockObject\MockObject as Mock;
use PHPUnit\Framework\TestCase;
use ProxyManager\Factory\LazyLoadingGhostFactory;
use ProxyManager\Proxy\GhostObjectInterface;
use ProxyManager\Proxy\LazyLoadingInterface;
use ProxyManager\ProxyGenerator\Util\Properties;
use ProxyManagerTestAsset\BaseClass;
use ProxyManagerTestAsset\CallableInterface;
use ProxyManagerTestAsset\ClassWithAbstractPublicMethod;
use ProxyManagerTestAsset\ClassWithCollidingPrivateInheritedProperties;
use ProxyManagerTestAsset\ClassWithCounterConstructor;
use ProxyManagerTestAsset\ClassWithDynamicArgumentsMethod;
use ProxyManagerTestAsset\ClassWithMethodWithByRefVariadicFunction;
use ProxyManagerTestAsset\ClassWithMethodWithVariadicFunction;
use ProxyManagerTestAsset\ClassWithMixedProperties;
use ProxyManagerTestAsset\ClassWithMixedPropertiesAndAccessorMethods;
use ProxyManagerTestAsset\ClassWithMixedTypedProperties;
use ProxyManagerTestAsset\ClassWithParentHint;
use ProxyManagerTestAsset\ClassWithPrivateProperties;
use ProxyManagerTestAsset\ClassWithProtectedProperties;
use ProxyManagerTestAsset\ClassWithPublicArrayProperty;
use ProxyManagerTestAsset\ClassWithPublicProperties;
use ProxyManagerTestAsset\ClassWithSelfHint;
use ProxyManagerTestAsset\EmptyClass;
use ProxyManagerTestAsset\OtherObjectAccessClass;
use ProxyManagerTestAsset\VoidCounter;
use ReflectionClass;
use ReflectionProperty;
use stdClass;
use function array_key_exists;
use function array_values;
use function get_class;
use function get_parent_class;
use function random_int;
use function serialize;
use function sprintf;
use function str_replace;
use function uniqid;
use function unserialize;

/**
 * Tests for {@see \ProxyManager\ProxyGenerator\LazyLoadingGhostGenerator} produced objects
 *
 * @group Functional
 * @coversNothing
 */
final class LazyLoadingGhostFunctionalTest extends TestCase
{
    /**
     * @param mixed[] $params
     * @param mixed   $expectedValue
     *
     * @dataProvider getProxyInitializingMethods
     *
     * @psalm-template OriginalClass
     * @psalm-param class-string<OriginalClass> $className
     * @psalm-param OriginalClass $instance
     */
    public function testMethodCallsThatLazyLoadTheObject(
        string $className,
        object $instance,
        string $method,
        array $params,
        $expectedValue
    ) : void {
        $proxy = (new LazyLoadingGhostFactory())
            ->createProxy($className, $this->createInitializer($className, $instance));

        self::assertFalse($proxy->isProxyInitialized());

        $callProxyMethod = [$proxy, $method];
        $parameterValues = array_values($params);

        self::assertIsCallable($callProxyMethod);
        self::assertSame($expectedValue, $callProxyMethod(...$parameterValues));
        self::assertTrue($proxy->isProxyInitialized());
    }

    /**
     * @param mixed[] $params
     * @param mixed   $expectedValue
     *
     * @dataProvider getProxyNonInitializingMethods
     *
     * @psalm-template OriginalClass
     * @psalm-param class-string<OriginalClass> $className
     * @psalm-param OriginalClass $instance
     */
    public function testMethodCallsThatDoNotLazyLoadTheObject(
        string $className,
        object $instance,
        string $method,
        array $params,
        $expectedValue
    ) : void {
        $initializeMatcher = $this->createMock(CallableInterface::class);

        $initializeMatcher->expects(self::never())->method('__invoke'); // should not initialize the proxy

        $proxy = (new LazyLoadingGhostFactory())->createProxy(
            $className,
            $this->createInitializer($className, $instance, $initializeMatcher)
        );

        self::assertFalse($proxy->isProxyInitialized());

        $callProxyMethod = [$proxy, $method];
        $parameterValues = array_values($params);

        self::assertIsCallable($callProxyMethod);
        self::assertSame($expectedValue, $callProxyMethod(...$parameterValues));
        self::assertFalse($proxy->isProxyInitialized());
    }

    /**
     * @param mixed[] $params
     * @param mixed   $expectedValue
     *
     * @dataProvider getProxyMethods
     *
     * @psalm-template OriginalClass
     * @psalm-param class-string<OriginalClass> $className
     * @psalm-param OriginalClass $instance
     */
    public function testMethodCallsAfterUnSerialization(
        string $className,
        object $instance,
        string $method,
        array $params,
        $expectedValue
    ) : void {
        /** @var GhostObjectInterface $proxy */
        $proxy = unserialize(serialize((new LazyLoadingGhostFactory())->createProxy(
            $className,
            $this->createInitializer($className, $instance)
        )));

        self::assertTrue($proxy->isProxyInitialized());

        $callProxyMethod = [$proxy, $method];
        $parameterValues = array_values($params);

        self::assertIsCallable($callProxyMethod);
        self::assertSame($expectedValue, $callProxyMethod(...$parameterValues));
    }

    /**
     * @param mixed[] $params
     * @param mixed   $expectedValue
     *
     * @dataProvider getProxyMethods
     *
     * @psalm-template OriginalClass
     * @psalm-param class-string<OriginalClass> $className
     * @psalm-param OriginalClass $instance
     */
    public function testMethodCallsAfterCloning(
        string $className,
        object $instance,
        string $method,
        array $params,
        $expectedValue
    ) : void {
        $proxy  = (new LazyLoadingGhostFactory())->createProxy(
            $className,
            $this->createInitializer($className, $instance)
        );
        $cloned = clone $proxy;

        self::assertTrue($cloned->isProxyInitialized());

        $callProxyMethod = [$proxy, $method];
        $parameterValues = array_values($params);

        self::assertIsCallable($callProxyMethod);
        self::assertSame($expectedValue, $callProxyMethod(...$parameterValues));
    }

    /**
     * @param mixed $propertyValue
     *
     * @dataProvider getPropertyAccessProxies
     */
    public function testPropertyReadAccess(
        object $instance,
        GhostObjectInterface $proxy,
        string $publicProperty,
        $propertyValue
    ) : void {
        self::assertSame($propertyValue, $proxy->$publicProperty);
        self::assertTrue($proxy->isProxyInitialized());
    }

    /**
     * @dataProvider getPropertyAccessProxies
     */
    public function testPropertyWriteAccess(object $instance, GhostObjectInterface $proxy, string $publicProperty) : void
    {
        $newValue               = uniqid('', true);
        $proxy->$publicProperty = $newValue;

        self::assertTrue($proxy->isProxyInitialized());
        self::assertSame($newValue, $proxy->$publicProperty);
    }

    /**
     * @dataProvider getPropertyAccessProxies
     */
    public function testPropertyExistence(object $instance, GhostObjectInterface $proxy, string $publicProperty) : void
    {
        self::assertSame(isset($instance->$publicProperty), isset($proxy->$publicProperty));
        self::assertTrue($proxy->isProxyInitialized());
    }

    /**
     * @dataProvider getPropertyAccessProxies
     */
    public function testPropertyAbsence(object $instance, GhostObjectInterface $proxy, string $publicProperty) : void
    {
        $proxy->$publicProperty = null;
        self::assertFalse(isset($proxy->$publicProperty));
        self::assertTrue($proxy->isProxyInitialized());
    }

    /**
     * @dataProvider getPropertyAccessProxies
     */
    public function testPropertyUnset(object $instance, GhostObjectInterface $proxy, string $publicProperty) : void
    {
        unset($proxy->$publicProperty);

        self::assertTrue($proxy->isProxyInitialized());
        self::assertTrue(isset($instance->$publicProperty));
        self::assertFalse(isset($proxy->$publicProperty));
    }

    /**
     * Verifies that accessing a public property containing an array behaves like in a normal context
     */
    public function testCanWriteToArrayKeysInPublicProperty() : void
    {
        $proxy = (new LazyLoadingGhostFactory())->createProxy(
            ClassWithPublicArrayProperty::class,
            $this->createInitializer(ClassWithPublicArrayProperty::class, new ClassWithPublicArrayProperty())
        );

        $proxy->arrayProperty['foo'] = 'bar';

        self::assertByRefVariableValueSame('bar', $proxy->arrayProperty['foo']);

        $proxy->arrayProperty = ['tab' => 'taz'];

        self::assertSame(['tab' => 'taz'], $proxy->arrayProperty);
    }

    /**
     * Verifies that public properties retrieved via `__get` don't get modified in the object itself
     */
    public function testWillNotModifyRetrievedPublicProperties() : void
    {
        $proxy    = (new LazyLoadingGhostFactory())->createProxy(
            ClassWithPublicProperties::class,
            $this->createInitializer(ClassWithPublicProperties::class, new ClassWithPublicProperties())
        );
        $variable = $proxy->property0;

        self::assertByRefVariableValueSame('property0', $variable);

        $variable = 'foo';

        self::assertSame('property0', $proxy->property0);
        self::assertByRefVariableValueSame('foo', $variable);
    }

    /**
     * Verifies that public properties references retrieved via `__get` modify in the object state
     */
    public function testWillModifyByRefRetrievedPublicProperties() : void
    {
        $instance = new ClassWithPublicProperties();
        $proxy    = (new LazyLoadingGhostFactory())->createProxy(
            ClassWithPublicProperties::class,
            $this->createInitializer(ClassWithPublicProperties::class, $instance)
        );
        $variable = & $proxy->property0;

        self::assertByRefVariableValueSame('property0', $variable);

        $variable = 'foo';

        self::assertSame('foo', $proxy->property0);
        self::assertByRefVariableValueSame('foo', $variable);
    }

    public function testKeepsInitializerWhenNotOverwitten() : void
    {
        $initializer = static function () : bool {
            return true;
        };

        $proxy = (new LazyLoadingGhostFactory())->createProxy(
            BaseClass::class,
            $initializer
        );

        $proxy->initializeProxy();

        self::assertSame($initializer, $proxy->getProxyInitializer());
    }

    /**
     * Verifies that public properties are not being initialized multiple times
     */
    public function testKeepsInitializedPublicProperties() : void
    {
        $proxy = (new LazyLoadingGhostFactory())->createProxy(
            BaseClass::class,
            static function (
                object $proxy,
                string $method,
                array $parameters,
                ?Closure & $initializer
            ) : bool {
                $initializer           = null;
                $proxy->publicProperty = 'newValue';

                return true;
            }
        );

        $proxy->initializeProxy();
        self::assertSame('newValue', $proxy->publicProperty);

        $proxy->publicProperty = 'otherValue';

        $proxy->initializeProxy();

        self::assertSame('otherValue', $proxy->publicProperty);
    }

    /**
     * Verifies that properties' default values are preserved
     */
    public function testPublicPropertyDefaultWillBePreserved() : void
    {
        $proxy = (new LazyLoadingGhostFactory())->createProxy(
            ClassWithPublicProperties::class,
            static function () : bool {
                return true;
            }
        );

        self::assertSame('property0', $proxy->property0);
    }

    /**
     * Verifies that protected properties' default values are preserved
     */
    public function testProtectedPropertyDefaultWillBePreserved() : void
    {
        $proxy = (new LazyLoadingGhostFactory())->createProxy(
            ClassWithProtectedProperties::class,
            static function () : bool {
                return true;
            }
        );

        // Check protected property via reflection
        $reflectionProperty = new ReflectionProperty(ClassWithProtectedProperties::class, 'property0');
        $reflectionProperty->setAccessible(true);

        self::assertSame('property0', $reflectionProperty->getValue($proxy));
    }

    /**
     * Verifies that private properties' default values are preserved
     */
    public function testPrivatePropertyDefaultWillBePreserved() : void
    {
        $proxy = (new LazyLoadingGhostFactory())->createProxy(
            ClassWithPrivateProperties::class,
            static function () : bool {
                return true;
            }
        );

        // Check protected property via reflection
        $reflectionProperty = new ReflectionProperty(ClassWithPrivateProperties::class, 'property0');
        $reflectionProperty->setAccessible(true);

        self::assertSame('property0', $reflectionProperty->getValue($proxy));
    }

    /**
     * @group 159
     * @group 192
     */
    public function testMultiLevelPrivatePropertiesDefaultsWillBePreserved() : void
    {
        $proxy = (new LazyLoadingGhostFactory())->createProxy(
            ClassWithCollidingPrivateInheritedProperties::class,
            static function () : bool {
                return true;
            }
        );

        $childProperty  = new ReflectionProperty(ClassWithCollidingPrivateInheritedProperties::class, 'property0');
        $parentProperty = new ReflectionProperty(get_parent_class(ClassWithCollidingPrivateInheritedProperties::class), 'property0');

        $childProperty->setAccessible(true);
        $parentProperty->setAccessible(true);

        self::assertSame('childClassProperty0', $childProperty->getValue($proxy));
        self::assertSame('property0', $parentProperty->getValue($proxy));
    }

    /**
     * @group 159
     * @group 192
     */
    public function testMultiLevelPrivatePropertiesByRefInitialization() : void
    {
        $proxy = (new LazyLoadingGhostFactory())->createProxy(
            ClassWithCollidingPrivateInheritedProperties::class,
            static function (
                GhostObjectInterface $proxy,
                string $method,
                array $params,
                ?Closure & $initializer,
                array $properties
            ) : bool {
                $initializer                                                                                              = null;
                $properties["\0" . ClassWithCollidingPrivateInheritedProperties::class . "\0property0"]                   = 'foo';
                $properties["\0" . get_parent_class(ClassWithCollidingPrivateInheritedProperties::class) . "\0property0"] = 'bar';

                return true;
            }
        );

        $childProperty  = new ReflectionProperty(ClassWithCollidingPrivateInheritedProperties::class, 'property0');
        $parentProperty = new ReflectionProperty(get_parent_class(ClassWithCollidingPrivateInheritedProperties::class), 'property0');

        $childProperty->setAccessible(true);
        $parentProperty->setAccessible(true);

        self::assertSame('foo', $childProperty->getValue($proxy));
        self::assertSame('bar', $parentProperty->getValue($proxy));
    }

    /**
     * @group 159
     * @group 192
     *
     * Test designed to verify that the cached logic does take into account the fact that
     * proxies are different instances
     */
    public function testGetPropertyFromDifferentProxyInstances() : void
    {
        $factory = new LazyLoadingGhostFactory();
        $proxy1  = $factory->createProxy(
            ClassWithCollidingPrivateInheritedProperties::class,
            static function (
                GhostObjectInterface $proxy,
                string $method,
                array $params,
                ?Closure & $initializer,
                array $properties
            ) : bool {
                $initializer                                                                                              = null;
                $properties["\0" . ClassWithCollidingPrivateInheritedProperties::class . "\0property0"]                   = 'foo';
                $properties["\0" . get_parent_class(ClassWithCollidingPrivateInheritedProperties::class) . "\0property0"] = 'bar';

                return true;
            }
        );
        $proxy2  = $factory->createProxy(
            ClassWithCollidingPrivateInheritedProperties::class,
            static function (
                GhostObjectInterface $proxy,
                string $method,
                array $params,
                ?Closure & $initializer,
                array $properties
            ) : bool {
                $initializer                                                                                              = null;
                $properties["\0" . ClassWithCollidingPrivateInheritedProperties::class . "\0property0"]                   = 'baz';
                $properties["\0" . get_parent_class(ClassWithCollidingPrivateInheritedProperties::class) . "\0property0"] = 'tab';

                return true;
            }
        );

        $childProperty  = new ReflectionProperty(ClassWithCollidingPrivateInheritedProperties::class, 'property0');
        $parentProperty = new ReflectionProperty(get_parent_class(ClassWithCollidingPrivateInheritedProperties::class), 'property0');

        $childProperty->setAccessible(true);
        $parentProperty->setAccessible(true);

        self::assertSame('foo', $childProperty->getValue($proxy1));
        self::assertSame('bar', $parentProperty->getValue($proxy1));

        self::assertSame('baz', $childProperty->getValue($proxy2));
        self::assertSame('tab', $parentProperty->getValue($proxy2));
    }

    /**
     * @group 159
     * @group 192
     *
     * Test designed to verify that the cached logic does take into account the fact that
     * proxies are different instances
     */
    public function testSetPrivatePropertyOnDifferentProxyInstances() : void
    {
        $factory = new LazyLoadingGhostFactory();
        $proxy1  = $factory->createProxy(
            ClassWithMixedPropertiesAndAccessorMethods::class,
            static function (
                GhostObjectInterface $proxy,
                string $method,
                array $params,
                ?Closure & $initializer
            ) : bool {
                $initializer = null;

                return true;
            }
        );
        $proxy2  = $factory->createProxy(
            ClassWithMixedPropertiesAndAccessorMethods::class,
            static function (
                GhostObjectInterface $proxy,
                string $method,
                array $params,
                ?Closure & $initializer
            ) : bool {
                $initializer = null;

                return true;
            }
        );

        $proxy1->set('privateProperty', 'private1');
        $proxy2->set('privateProperty', 'private2');
        self::assertSame('private1', $proxy1->get('privateProperty'));
        self::assertSame('private2', $proxy2->get('privateProperty'));
    }

    /**
     * @group 159
     * @group 192
     *
     * Test designed to verify that the cached logic does take into account the fact that
     * proxies are different instances
     */
    public function testIssetPrivatePropertyOnDifferentProxyInstances() : void
    {
        $factory = new LazyLoadingGhostFactory();
        $proxy1  = $factory->createProxy(
            ClassWithMixedPropertiesAndAccessorMethods::class,
            static function (
                GhostObjectInterface $proxy,
                string $method,
                array $params,
                ?Closure & $initializer
            ) : bool {
                $initializer = null;

                return true;
            }
        );
        $proxy2  = $factory->createProxy(
            ClassWithMixedPropertiesAndAccessorMethods::class,
            static function (
                GhostObjectInterface $proxy,
                string $method,
                array $params,
                ?Closure & $initializer,
                array $properties
            ) : bool {
                $initializer                                                                                = null;
                $properties["\0" . ClassWithMixedPropertiesAndAccessorMethods::class . "\0privateProperty"] = null;

                return true;
            }
        );

        self::assertTrue($proxy1->has('privateProperty'));
        self::assertFalse($proxy2->has('privateProperty'));
        self::assertTrue($proxy1->has('privateProperty'));
        self::assertFalse($proxy2->has('privateProperty'));
    }

    /**
     * @group 159
     * @group 192
     *
     * Test designed to verify that the cached logic does take into account the fact that
     * proxies are different instances
     */
    public function testUnsetPrivatePropertyOnDifferentProxyInstances() : void
    {
        $factory = new LazyLoadingGhostFactory();
        $proxy1  = $factory->createProxy(
            ClassWithMixedPropertiesAndAccessorMethods::class,
            static function (
                GhostObjectInterface $proxy,
                string $method,
                array $params,
                ?Closure & $initializer
            ) : bool {
                $initializer = null;

                return true;
            }
        );
        $proxy2  = $factory->createProxy(
            ClassWithMixedPropertiesAndAccessorMethods::class,
            static function (
                GhostObjectInterface $proxy,
                string $method,
                array $params,
                ?Closure & $initializer
            ) : bool {
                $initializer = null;

                return true;
            }
        );

        self::assertTrue($proxy1->has('privateProperty'));
        $proxy2->remove('privateProperty');
        self::assertFalse($proxy2->has('privateProperty'));
        self::assertTrue($proxy1->has('privateProperty'));
        $proxy1->remove('privateProperty');
        self::assertFalse($proxy1->has('privateProperty'));
        self::assertFalse($proxy2->has('privateProperty'));
    }

    /**
     * @group 159
     * @group 192
     *
     * Test designed to verify that the cached logic does take into account the fact that
     * proxies are different instances
     */
    public function testIssetPrivateAndProtectedPropertiesDoesCheckAgainstBooleanFalse() : void
    {
        $factory = new LazyLoadingGhostFactory();
        $proxy1  = $factory->createProxy(
            ClassWithMixedPropertiesAndAccessorMethods::class,
            static function (
                GhostObjectInterface $proxy,
                string $method,
                array $params,
                ?Closure & $initializer,
                array $properties
            ) : bool {
                $initializer                                                                                = null;
                $properties['publicProperty']                                                               = false;
                $properties["\0*\0protectedProperty"]                                                       = false;
                $properties["\0" . ClassWithMixedPropertiesAndAccessorMethods::class . "\0privateProperty"] = false;

                return true;
            }
        );
        $proxy2  = $factory->createProxy(
            ClassWithMixedPropertiesAndAccessorMethods::class,
            static function (
                GhostObjectInterface $proxy,
                string $method,
                array $params,
                ?Closure & $initializer,
                array $properties
            ) : bool {
                $initializer                                                                                = null;
                $properties['publicProperty']                                                               = null;
                $properties["\0*\0protectedProperty"]                                                       = null;
                $properties["\0" . ClassWithMixedPropertiesAndAccessorMethods::class . "\0privateProperty"] = null;

                return true;
            }
        );

        self::assertTrue($proxy1->has('protectedProperty'));
        self::assertTrue($proxy1->has('publicProperty'));
        self::assertTrue($proxy1->has('privateProperty'));

        self::assertFalse($proxy2->has('protectedProperty'));
        self::assertFalse($proxy2->has('publicProperty'));
        self::assertFalse($proxy2->has('privateProperty'));
    }

    public function testByRefInitialization() : void
    {
        $proxy = (new LazyLoadingGhostFactory())->createProxy(
            ClassWithMixedProperties::class,
            static function (
                GhostObjectInterface $proxy,
                string $method,
                array $params,
                ?Closure & $initializer,
                array $properties
            ) : bool {
                $initializer                                                               = null;
                $properties["\0" . ClassWithMixedProperties::class . "\0privateProperty0"] = 'private0';
                $properties["\0" . ClassWithMixedProperties::class . "\0privateProperty1"] = 'private1';
                $properties["\0" . ClassWithMixedProperties::class . "\0privateProperty2"] = 'private2';
                $properties["\0*\0protectedProperty0"]                                     = 'protected0';
                $properties["\0*\0protectedProperty1"]                                     = 'protected1';
                $properties["\0*\0protectedProperty2"]                                     = 'protected2';
                $properties['publicProperty0']                                             = 'public0';
                $properties['publicProperty1']                                             = 'public1';
                $properties['publicProperty2']                                             = 'public2';

                return true;
            }
        );

        $reflectionClass = new ReflectionClass(ClassWithMixedProperties::class);

        foreach (Properties::fromReflectionClass($reflectionClass)->getInstanceProperties() as $property) {
            $property->setAccessible(true);

            self::assertSame(str_replace('Property', '', $property->getName()), $property->getValue($proxy));
        }
    }

    public function testByRefInitializationOfTypedProperties() : void
    {
        $proxy = (new LazyLoadingGhostFactory())->createProxy(
            ClassWithMixedTypedProperties::class,
            static function (
                GhostObjectInterface $proxy,
                string $method,
                array $params,
                ?Closure & $initializer,
                array $properties
            ) : bool {
                $initializer                                                                         = null;
                $properties["\0" . ClassWithMixedTypedProperties::class . "\0privateStringProperty"] = 'private0';
                $properties["\0*\0protectedStringProperty"]                                          = 'protected0';
                $properties['publicStringProperty']                                                  = 'public0';

                return true;
            }
        );

        $reflectionClass = new ReflectionClass(ClassWithMixedTypedProperties::class);

        $properties = Properties::fromReflectionClass($reflectionClass)->getInstanceProperties();

        $privateProperty   = $properties["\0" . ClassWithMixedTypedProperties::class . "\0privateStringProperty"];
        $protectedProperty = $properties["\0*\0protectedStringProperty"];

        $privateProperty->setAccessible(true);
        $protectedProperty->setAccessible(true);

        self::assertSame('private0', $privateProperty->getValue($proxy));
        self::assertSame('protected0', $properties["\0*\0protectedStringProperty"]->getValue($proxy));
        self::assertSame('public0', $proxy->publicStringProperty);
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
            (new LazyLoadingGhostFactory())
                ->createProxy(
                    ClassWithCounterConstructor::class,
                    static function () : bool {
                        return true;
                    }
                )
        );

        $proxy = new $proxyName(15);

        self::assertSame(15, $proxy->amount, 'Verifying that the proxy constructor works as expected');
        self::assertSame(15, $proxy->getAmount(), 'Verifying that the proxy constructor works as expected');
        $proxy->__construct(5);
        self::assertSame(20, $proxy->amount, 'Verifying that the proxy constructor works as expected');
        self::assertSame(20, $proxy->getAmount(), 'Verifying that the proxy constructor works as expected');
    }

    public function testInitializeProxyWillReturnTrueOnSuccessfulInitialization() : void
    {
        $proxy = (new LazyLoadingGhostFactory())->createProxy(
            ClassWithMixedTypedProperties::class,
            $this->createInitializer(
                ClassWithMixedTypedProperties::class,
                new ClassWithMixedTypedProperties()
            )
        );

        self::assertTrue($proxy->initializeProxy());
        self::assertTrue($proxy->isProxyInitialized());
        self::assertFalse($proxy->initializeProxy());
    }

    /**
     * @psalm-param (CallableInterface&Mock)|null $initializerMatcher
     * @psalm-return Closure(
     *   GhostObjectInterface,
     *   string,
     *   array,
     *   ?Closure
     * ) : bool
     */
    private function createInitializer(string $className, object $realInstance, ?Mock $initializerMatcher = null) : Closure
    {
        if (! $initializerMatcher) {
            $initializerMatcher = $this->createMock(CallableInterface::class);

            $initializerMatcher
                ->expects(self::once())
                ->method('__invoke')
                ->with(self::logicalAnd(
                    self::isInstanceOf(GhostObjectInterface::class),
                    self::isInstanceOf($className)
                ));
        }

        return static function (
            GhostObjectInterface $proxy,
            string $method,
            array $params,
            ?Closure & $initializer
        ) use (
            $initializerMatcher,
            $realInstance
        ) : bool {
            $initializer = null;

            $reflectionClass = new ReflectionClass($realInstance);

            foreach (Properties::fromReflectionClass($reflectionClass)->getInstanceProperties() as $property) {
                if (! self::isPropertyInitialized($realInstance, $property)) {
                    continue;
                }

                $property->setAccessible(true);
                $property->setValue($proxy, $property->getValue($realInstance));
            }

            $initializerMatcher->__invoke($proxy, $method, $params);

            return true;
        };
    }

    /**
     * Generates a list of object | invoked method | parameters | expected result
     *
     * @return null[][]|string[][]|object[][]|mixed[][][]
     */
    public function getProxyMethods() : array
    {
        $selfHintParam = new ClassWithSelfHint();
        $empty         = new EmptyClass();

        return [
            [
                BaseClass::class,
                new BaseClass(),
                'publicMethod',
                [],
                'publicMethodDefault',
            ],
            [
                BaseClass::class,
                new BaseClass(),
                'publicTypeHintedMethod',
                [new stdClass()],
                'publicTypeHintedMethodDefault',
            ],
            [
                BaseClass::class,
                new BaseClass(),
                'publicByReferenceMethod',
                [],
                'publicByReferenceMethodDefault',
            ],
            [
                ClassWithSelfHint::class,
                new ClassWithSelfHint(),
                'selfHintMethod',
                ['parameter' => $selfHintParam],
                $selfHintParam,
            ],
            [
                ClassWithParentHint::class,
                new ClassWithParentHint(),
                'parentHintMethod',
                ['parameter' => $empty],
                $empty,
            ],
            [
                ClassWithAbstractPublicMethod::class,
                new EmptyClass(), // EmptyClass just used to not make reflection explode when synchronizing properties
                'publicAbstractMethod',
                [],
                null,
            ],
            [
                ClassWithMethodWithByRefVariadicFunction::class,
                new ClassWithMethodWithByRefVariadicFunction(),
                'tuz',
                ['Ocramius', 'Malukenho'],
                ['Ocramius', 'changed'],
            ],
        ];
    }

    /**
     * Generates a list of object | invoked method | parameters | expected result for methods that cause lazy-loading
     * of a ghost object
     *
     * @return string[][]|object[][]|mixed[][][]|null[][]
     */
    public function getProxyInitializingMethods() : array
    {
        return [
            [
                BaseClass::class,
                new BaseClass(),
                'publicPropertyGetter',
                [],
                'publicPropertyDefault',
            ],
            [
                BaseClass::class,
                new BaseClass(),
                'protectedPropertyGetter',
                [],
                'protectedPropertyDefault',
            ],
            [
                BaseClass::class,
                new BaseClass(),
                'privatePropertyGetter',
                [],
                'privatePropertyDefault',
            ],
            [
                ClassWithMethodWithVariadicFunction::class,
                new ClassWithMethodWithVariadicFunction(),
                'foo',
                ['Ocramius', 'Malukenho'],
                null,
            ],
        ];
    }

    /**
     * Generates a list of object | invoked method | parameters | expected result for methods DON'T cause lazy-loading
     *
     * @return null[][]|string[][]|object[][]|mixed[][][]
     */
    public function getProxyNonInitializingMethods() : array
    {
        return $this->getProxyMethods();
    }

    /**
     * Generates proxies and instances with a public property to feed to the property accessor methods
     *
     * @return string[][]|object[][]
     */
    public function getPropertyAccessProxies() : array
    {
        $instance1 = new BaseClass();
        $instance2 = new BaseClass();

        $factory = new LazyLoadingGhostFactory();

        /** @var GhostObjectInterface $serialized */
        $serialized = unserialize(serialize($factory->createProxy(
            BaseClass::class,
            $this->createInitializer(BaseClass::class, $instance2)
        )));

        return [
            [
                $instance1,
                $factory->createProxy(
                    BaseClass::class,
                    $this->createInitializer(BaseClass::class, $instance1)
                ),
                'publicProperty',
                'publicPropertyDefault',
            ],
            [
                $instance2,
                $serialized,
                'publicProperty',
                'publicPropertyDefault',
            ],
        ];
    }

    /**
     * @param mixed   $expected
     * @param mixed[] $proxyOptions
     *
     * @dataProvider skipPropertiesFixture
     *
     * @psalm-param class-string $className
     * @psalm-param array{skippedProperties?: array<int, string>} $proxyOptions
     */
    public function testInitializationIsSkippedForSkippedProperties(
        string $className,
        string $propertyClass,
        string $propertyName,
        array $proxyOptions,
        $expected
    ) : void {
        $ghostObject = (new LazyLoadingGhostFactory())->createProxy(
            $className,
            static function () use ($propertyName) : bool {
                self::fail(sprintf('The Property "%s" was not expected to be lazy-loaded', $propertyName));

                return true;
            },
            $proxyOptions
        );

        $property = new ReflectionProperty($propertyClass, $propertyName);
        $property->setAccessible(true);

        self::assertSame($expected, $property->getValue($ghostObject));
    }

    /**
     * @param array<string, mixed> $proxyOptions
     *
     * @dataProvider skipPropertiesFixture
     *
     * @psalm-param class-string $className
     * @psalm-param array{skippedProperties?: array<int, string>} $proxyOptions
     */
    public function testSkippedPropertiesAreNotOverwrittenOnInitialization(
        string $className,
        string $propertyClass,
        string $propertyName,
        array $proxyOptions
    ) : void {
        $ghostObject = (new LazyLoadingGhostFactory())->createProxy(
            $className,
            static function (
                GhostObjectInterface $proxy,
                string $method,
                array $params,
                ?Closure & $initializer
            ) : bool {
                $initializer = null;

                return true;
            },
            $proxyOptions
        );

        $property = new ReflectionProperty($propertyClass, $propertyName);

        $property->setAccessible(true);

        $value = uniqid('', true);

        $property->setValue($ghostObject, $value);

        self::assertTrue($ghostObject->initializeProxy());

        self::assertSame(
            $value,
            $property->getValue($ghostObject),
            'Property should not be changed by proxy initialization'
        );
    }

    /**
     * @group 265
     */
    public function testWillForwardVariadicByRefArguments() : void
    {
        $object = (new LazyLoadingGhostFactory())->createProxy(
            ClassWithMethodWithByRefVariadicFunction::class,
            static function (
                GhostObjectInterface $proxy,
                string $method,
                array $params,
                ?Closure & $initializer
            ) : bool {
                $initializer = null;

                return true;
            }
        );

        $parameters = ['a', 'b', 'c'];

        // first, testing normal variadic behavior (verifying we didn't screw up in the test asset)
        self::assertSame(['a', 'changed', 'c'], (new ClassWithMethodWithByRefVariadicFunction())->tuz(...$parameters));
        self::assertSame(['a', 'changed', 'c'], $object->tuz(...$parameters));
        self::assertSame(['a', 'changed', 'c'], $parameters, 'by-ref variadic parameter was changed');
    }

    /**
     * @group 265
     */
    public function testWillForwardDynamicArguments() : void
    {
        $object = (new LazyLoadingGhostFactory())->createProxy(
            ClassWithDynamicArgumentsMethod::class,
            static function () : bool {
                return true;
            }
        );

        // first, testing normal variadic behavior (verifying we didn't screw up in the test asset)
        self::assertSame(['a', 'b'], (new ClassWithDynamicArgumentsMethod())->dynamicArgumentsMethod('a', 'b'));
        self::assertSame(['a', 'b'], $object->dynamicArgumentsMethod('a', 'b'));
    }

    /**
     * @return mixed[] in order:
     *                  - the class to be proxied
     *                  - the class owning the property to be checked
     *                  - the property name
     *                  - the options to be passed to the generator
     *                  - the expected value of the property
     */
    public function skipPropertiesFixture() : array
    {
        return [
            [
                ClassWithPublicProperties::class,
                ClassWithPublicProperties::class,
                'property9',
                [
                    'skippedProperties' => ['property9'],
                ],
                'property9',
            ],
            [
                ClassWithProtectedProperties::class,
                ClassWithProtectedProperties::class,
                'property9',
                [
                    'skippedProperties' => ["\0*\0property9"],
                ],
                'property9',
            ],
            [
                ClassWithPrivateProperties::class,
                ClassWithPrivateProperties::class,
                'property9',
                [
                    'skippedProperties' => ["\0ProxyManagerTestAsset\\ClassWithPrivateProperties\0property9"],
                ],
                'property9',
            ],
            [
                ClassWithCollidingPrivateInheritedProperties::class,
                ClassWithCollidingPrivateInheritedProperties::class,
                'property0',
                [
                    'skippedProperties' => ["\0ProxyManagerTestAsset\\ClassWithCollidingPrivateInheritedProperties\0property0"],
                ],
                'childClassProperty0',
            ],
            [
                ClassWithCollidingPrivateInheritedProperties::class,
                ClassWithPrivateProperties::class,
                'property0',
                [
                    'skippedProperties' => ["\0ProxyManagerTestAsset\\ClassWithPrivateProperties\0property0"],
                ],
                'property0',
            ],
        ];
    }

    /**
     * @group        276
     * @dataProvider getMethodsThatAccessPropertiesOnOtherObjectsInTheSameScope
     */
    public function testWillLazyLoadMembersOfOtherProxiesWithTheSamePrivateScope(
        object $callerObject,
        string $method,
        string $propertyIndex,
        string $expectedValue
    ) : void {
        $proxy = (new LazyLoadingGhostFactory())->createProxy(
            OtherObjectAccessClass::class,
            static function (
                GhostObjectInterface $proxy,
                string $method,
                array $params,
                ?Closure & $initializer,
                array $properties
            ) use (
                $propertyIndex,
                $expectedValue
            ) : bool {
                $initializer = null;

                $properties[$propertyIndex] = $expectedValue;

                return true;
            }
        );

        $accessor = [$callerObject, $method];

        self::assertIsCallable($accessor);

        self::assertFalse($proxy->isProxyInitialized());
        self::assertSame($expectedValue, $accessor($proxy));
        self::assertTrue($proxy->isProxyInitialized());
    }

    /**
     * @group        276
     * @dataProvider getMethodsThatAccessPropertiesOnOtherObjectsInTheSameScope
     */
    public function testWillAccessMembersOfOtherDeSerializedProxiesWithTheSamePrivateScope(
        object $callerObject,
        string $method,
        string $propertyIndex,
        string $expectedValue
    ) : void {
        /** @var OtherObjectAccessClass&LazyLoadingInterface $proxy */
        $proxy = unserialize(serialize(
            (new LazyLoadingGhostFactory())->createProxy(
                OtherObjectAccessClass::class,
                static function (
                    GhostObjectInterface $proxy,
                    string $method,
                    array $params,
                    ?Closure & $initializer,
                    array $properties
                ) use (
                    $propertyIndex,
                    $expectedValue
                ) : bool {
                    $initializer = null;

                    $properties[$propertyIndex] = $expectedValue;

                    return true;
                }
            )
        ));

        $accessor = [$callerObject, $method];

        self::assertIsCallable($accessor);

        self::assertTrue($proxy->isProxyInitialized());
        self::assertSame($expectedValue, $accessor($proxy));
    }

    /**
     * @group        276
     * @dataProvider getMethodsThatAccessPropertiesOnOtherObjectsInTheSameScope
     */
    public function testWillAccessMembersOfOtherClonedProxiesWithTheSamePrivateScope(
        object $callerObject,
        string $method,
        string $propertyIndex,
        string $expectedValue
    ) : void {
        $proxy = clone (new LazyLoadingGhostFactory())->createProxy(
            OtherObjectAccessClass::class,
            static function (
                GhostObjectInterface $proxy,
                string $method,
                array $params,
                ?Closure & $initializer,
                array $properties
            ) use (
                $propertyIndex,
                $expectedValue
            ) : bool {
                $initializer = null;

                $properties[$propertyIndex] = $expectedValue;

                return true;
            }
        );

        $accessor = [$callerObject, $method];

        self::assertIsCallable($accessor);

        self::assertTrue($proxy->isProxyInitialized());
        self::assertSame($expectedValue, $accessor($proxy));
    }

    /** @return string[][]|object[][] */
    public function getMethodsThatAccessPropertiesOnOtherObjectsInTheSameScope() : array
    {
        $factory = new LazyLoadingGhostFactory();

        return [
            OtherObjectAccessClass::class . '#$privateProperty'                => [
                new OtherObjectAccessClass(),
                'getPrivateProperty',
                "\0" . OtherObjectAccessClass::class . "\0privateProperty",
                uniqid('', true),
            ],
            OtherObjectAccessClass::class . '#$protectedProperty'              => [
                new OtherObjectAccessClass(),
                'getProtectedProperty',
                "\0*\0protectedProperty",
                uniqid('', true),
            ],
            OtherObjectAccessClass::class . '#$publicProperty'                 => [
                new OtherObjectAccessClass(),
                'getPublicProperty',
                'publicProperty',
                uniqid('', true),
            ],
            '(proxy) ' . OtherObjectAccessClass::class . '#$privateProperty'   => [
                $factory->createProxy(
                    OtherObjectAccessClass::class,
                    static function () : bool {
                        self::fail('Should never be initialized, as its values aren\'t accessed');

                        return true;
                    }
                ),
                'getPrivateProperty',
                "\0" . OtherObjectAccessClass::class . "\0privateProperty",
                uniqid('', true),
            ],
            '(proxy) ' . OtherObjectAccessClass::class . '#$protectedProperty' => [
                $factory->createProxy(
                    OtherObjectAccessClass::class,
                    static function () : bool {
                        self::fail('Should never be initialized, as its values aren\'t accessed');

                        return true;
                    }
                ),
                'getProtectedProperty',
                "\0*\0protectedProperty",
                uniqid('', true),
            ],
            '(proxy) ' . OtherObjectAccessClass::class . '#$publicProperty'    => [
                $factory->createProxy(
                    OtherObjectAccessClass::class,
                    static function () : bool {
                        self::fail('Should never be initialized, as its values aren\'t accessed');

                        return true;
                    }
                ),
                'getPublicProperty',
                'publicProperty',
                uniqid('', true),
            ],
        ];
    }

    /**
     * @group 276
     */
    public function testFriendObjectWillNotCauseLazyLoadingOnSkippedProperty() : void
    {
        $proxy = (new LazyLoadingGhostFactory())
            ->createProxy(
                OtherObjectAccessClass::class,
                static function () : bool {
                    throw new BadMethodCallException('The proxy should never be initialized, as all properties are skipped');
                },
                [
                    'skippedProperties' => [
                        "\0" . OtherObjectAccessClass::class . "\0privateProperty",
                        "\0*\0protectedProperty",
                        'publicProperty',
                    ],
                ]
            );

        $privatePropertyValue   = uniqid('', true);
        $protectedPropertyValue = uniqid('', true);
        $publicPropertyValue    = uniqid('', true);

        $reflectionPrivateProperty = new ReflectionProperty(OtherObjectAccessClass::class, 'privateProperty');

        $reflectionPrivateProperty->setAccessible(true);
        $reflectionPrivateProperty->setValue($proxy, $privatePropertyValue);

        $reflectionProtectedProperty = new ReflectionProperty(OtherObjectAccessClass::class, 'protectedProperty');

        $reflectionProtectedProperty->setAccessible(true);
        $reflectionProtectedProperty->setValue($proxy, $protectedPropertyValue);

        $proxy->publicProperty = $publicPropertyValue;

        $friendObject = new OtherObjectAccessClass();

        self::assertSame($privatePropertyValue, $friendObject->getPrivateProperty($proxy));
        self::assertSame($protectedPropertyValue, $friendObject->getProtectedProperty($proxy));
        self::assertSame($publicPropertyValue, $friendObject->getPublicProperty($proxy));
    }

    public function testClonedSkippedPropertiesArePreserved() : void
    {
        $proxy = (new LazyLoadingGhostFactory())
            ->createProxy(
                BaseClass::class,
                static function (GhostObjectInterface $proxy) : bool {
                    $proxy->setProxyInitializer(null);

                    return true;
                },
                [
                    'skippedProperties' => [
                        "\0" . BaseClass::class . "\0privateProperty",
                        "\0*\0protectedProperty",
                        'publicProperty',
                    ],
                ]
            );

        $reflectionPrivate   = new ReflectionProperty(BaseClass::class, 'privateProperty');
        $reflectionProtected = new ReflectionProperty(BaseClass::class, 'protectedProperty');

        $reflectionPrivate->setAccessible(true);
        $reflectionProtected->setAccessible(true);

        $privateValue   = uniqid('', true);
        $protectedValue = uniqid('', true);
        $publicValue    = uniqid('', true);

        $reflectionPrivate->setValue($proxy, $privateValue);
        $reflectionProtected->setValue($proxy, $protectedValue);
        $proxy->publicProperty = $publicValue;

        self::assertFalse($proxy->isProxyInitialized());

        $clone = clone $proxy;

        self::assertFalse($proxy->isProxyInitialized());
        self::assertTrue($clone->isProxyInitialized());

        self::assertSame($privateValue, $reflectionPrivate->getValue($proxy));
        self::assertSame($privateValue, $reflectionPrivate->getValue($clone));
        self::assertSame($protectedValue, $reflectionProtected->getValue($proxy));
        self::assertSame($protectedValue, $reflectionProtected->getValue($clone));
        self::assertSame($publicValue, $proxy->publicProperty);
        self::assertSame($publicValue, $clone->publicProperty);
    }

    /**
     * @group 327
     */
    public function testWillExecuteLogicInAVoidMethod() : void
    {
        $initialCounter = random_int(10, 1000);

        $proxy = (new LazyLoadingGhostFactory())->createProxy(
            VoidCounter::class,
            static function (
                LazyLoadingInterface $proxy,
                string $method,
                array $params,
                ?Closure & $initializer,
                array $properties
            ) use ($initialCounter) : bool {
                $initializer = null;

                $properties['counter'] = $initialCounter;

                return true;
            }
        );

        $increment = random_int(1001, 10000);

        $proxy->increment($increment);

        self::assertSame($initialCounter + $increment, $proxy->counter);
    }

    private static function isPropertyInitialized(object $object, ReflectionProperty $property) : bool
    {
        return array_key_exists(
            ($property->isProtected() ? "\0*\0" : '')
            . ($property->isPrivate() ? "\0" . $property->getDeclaringClass()->getName() . "\0" : '')
            . $property->getName(),
            (array) $object
        );
    }

    /**
     * @param mixed $expected
     * @param mixed $actual
     */
    private static function assertByRefVariableValueSame($expected, & $actual) : void
    {
        self::assertSame($expected, $actual);
    }
}
