<?php
/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license.
 */

declare(strict_types=1);

namespace ProxyManagerTest\Functional;

use PHPUnit_Framework_MockObject_MockObject as Mock;
use PHPUnit_Framework_TestCase;
use ProxyManager\Generator\ClassGenerator;
use ProxyManager\Generator\Util\UniqueIdentifierGenerator;
use ProxyManager\GeneratorStrategy\EvaluatingGeneratorStrategy;
use ProxyManager\Proxy\GhostObjectInterface;
use ProxyManager\Proxy\LazyLoadingInterface;
use ProxyManager\ProxyGenerator\LazyLoadingGhostGenerator;
use ProxyManager\ProxyGenerator\Util\Properties;
use ProxyManagerTestAsset\BaseClass;
use ProxyManagerTestAsset\ClassWithAbstractPublicMethod;
use ProxyManagerTestAsset\ClassWithCollidingPrivateInheritedProperties;
use ProxyManagerTestAsset\ClassWithCounterConstructor;
use ProxyManagerTestAsset\ClassWithDynamicArgumentsMethod;
use ProxyManagerTestAsset\ClassWithMethodWithByRefVariadicFunction;
use ProxyManagerTestAsset\ClassWithMethodWithVariadicFunction;
use ProxyManagerTestAsset\ClassWithMixedProperties;
use ProxyManagerTestAsset\ClassWithMixedPropertiesAndAccessorMethods;
use ProxyManagerTestAsset\ClassWithParentHint;
use ProxyManagerTestAsset\ClassWithPrivateProperties;
use ProxyManagerTestAsset\ClassWithProtectedProperties;
use ProxyManagerTestAsset\ClassWithPublicArrayProperty;
use ProxyManagerTestAsset\ClassWithPublicProperties;
use ProxyManagerTestAsset\ClassWithSelfHint;
use ProxyManagerTestAsset\EmptyClass;
use ProxyManagerTestAsset\OtherObjectAccessClass;
use ReflectionClass;
use ReflectionProperty;
use stdClass;

/**
 * Tests for {@see \ProxyManager\ProxyGenerator\LazyLoadingGhostGenerator} produced objects
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 *
 * @group Functional
 * @coversNothing
 */
class LazyLoadingGhostFunctionalTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider getProxyInitializingMethods
     *
     * @param string  $className
     * @param object  $instance
     * @param string  $method
     * @param mixed[] $params
     * @param mixed   $expectedValue
     */
    public function testMethodCallsThatLazyLoadTheObject(
        string $className,
        $instance,
        string $method,
        array $params,
        $expectedValue
    ) {
        $proxyName = $this->generateProxy($className);

        /* @var $proxy GhostObjectInterface */
        $proxy = $proxyName::staticProxyConstructor($this->createInitializer($className, $instance));

        self::assertFalse($proxy->isProxyInitialized());

        /* @var $callProxyMethod callable */
        $callProxyMethod = [$proxy, $method];
        $parameterValues = array_values($params);

        self::assertInternalType('callable', $callProxyMethod);
        self::assertSame($expectedValue, $callProxyMethod(...$parameterValues));
        self::assertTrue($proxy->isProxyInitialized());
    }

    /**
     * @dataProvider getProxyNonInitializingMethods
     *
     * @param string  $className
     * @param object  $instance
     * @param string  $method
     * @param mixed[] $params
     * @param mixed   $expectedValue
     */
    public function testMethodCallsThatDoNotLazyLoadTheObject(
        string $className,
        $instance,
        string $method,
        array $params,
        $expectedValue
    ) {
        $proxyName         = $this->generateProxy($className);
        $initializeMatcher = $this->getMockBuilder(stdClass::class)->setMethods(['__invoke'])->getMock();

        $initializeMatcher->expects(self::never())->method('__invoke'); // should not initialize the proxy

        /* @var $proxy GhostObjectInterface */
        $proxy = $proxyName::staticProxyConstructor(
            $this->createInitializer($className, $instance, $initializeMatcher)
        );

        self::assertFalse($proxy->isProxyInitialized());

        /* @var $callProxyMethod callable */
        $callProxyMethod = [$proxy, $method];
        $parameterValues = array_values($params);

        self::assertInternalType('callable', $callProxyMethod);
        self::assertSame($expectedValue, $callProxyMethod(...$parameterValues));
        self::assertFalse($proxy->isProxyInitialized());
    }

    /**
     * @dataProvider getProxyMethods
     *
     * @param string  $className
     * @param object  $instance
     * @param string  $method
     * @param mixed[] $params
     * @param mixed   $expectedValue
     */
    public function testMethodCallsAfterUnSerialization(
        string $className,
        $instance,
        string $method,
        array $params,
        $expectedValue
    ) {
        $proxyName = $this->generateProxy($className);

        /* @var $proxy GhostObjectInterface */
        $proxy = unserialize(serialize($proxyName::staticProxyConstructor(
            $this->createInitializer($className, $instance)
        )));

        self::assertTrue($proxy->isProxyInitialized());

        /* @var $callProxyMethod callable */
        $callProxyMethod = [$proxy, $method];
        $parameterValues = array_values($params);

        self::assertInternalType('callable', $callProxyMethod);
        self::assertSame($expectedValue, $callProxyMethod(...$parameterValues));
    }

    /**
     * @dataProvider getProxyMethods
     *
     * @param string  $className
     * @param object  $instance
     * @param string  $method
     * @param mixed[] $params
     * @param mixed   $expectedValue
     */
    public function testMethodCallsAfterCloning(
        string $className,
        $instance,
        string $method,
        array $params,
        $expectedValue
    ) {
        $proxyName = $this->generateProxy($className);

        /* @var $proxy GhostObjectInterface */
        $proxy  = $proxyName::staticProxyConstructor($this->createInitializer($className, $instance));
        $cloned = clone $proxy;

        self::assertTrue($cloned->isProxyInitialized());

        /* @var $callProxyMethod callable */
        $callProxyMethod = [$proxy, $method];
        $parameterValues = array_values($params);

        self::assertInternalType('callable', $callProxyMethod);
        self::assertSame($expectedValue, $callProxyMethod(...$parameterValues));
    }

    /**
     * @dataProvider getPropertyAccessProxies
     *
     * @param object               $instance
     * @param GhostObjectInterface $proxy
     * @param string               $publicProperty
     * @param mixed                $propertyValue
     */
    public function testPropertyReadAccess(
        $instance,
        GhostObjectInterface $proxy,
        string $publicProperty,
        $propertyValue
    ) {
        self::assertSame($propertyValue, $proxy->$publicProperty);
        self::assertTrue($proxy->isProxyInitialized());
    }

    /**
     * @dataProvider getPropertyAccessProxies
     *
     * @param object               $instance
     * @param GhostObjectInterface $proxy
     * @param string               $publicProperty
     */
    public function testPropertyWriteAccess($instance, GhostObjectInterface $proxy, string $publicProperty)
    {
        $newValue               = uniqid();
        $proxy->$publicProperty = $newValue;

        self::assertTrue($proxy->isProxyInitialized());
        self::assertSame($newValue, $proxy->$publicProperty);
    }

    /**
     * @dataProvider getPropertyAccessProxies
     *
     * @param object               $instance
     * @param GhostObjectInterface $proxy
     * @param string               $publicProperty
     */
    public function testPropertyExistence($instance, GhostObjectInterface $proxy, string $publicProperty)
    {
        self::assertSame(isset($instance->$publicProperty), isset($proxy->$publicProperty));
        self::assertTrue($proxy->isProxyInitialized());
    }

    /**
     * @dataProvider getPropertyAccessProxies
     *
     * @param object               $instance
     * @param GhostObjectInterface $proxy
     * @param string               $publicProperty
     */
    public function testPropertyAbsence($instance, GhostObjectInterface $proxy, string $publicProperty)
    {
        $proxy->$publicProperty = null;
        self::assertFalse(isset($proxy->$publicProperty));
        self::assertTrue($proxy->isProxyInitialized());
    }

    /**
     * @dataProvider getPropertyAccessProxies
     *
     * @param object               $instance
     * @param GhostObjectInterface $proxy
     * @param string               $publicProperty
     */
    public function testPropertyUnset($instance, GhostObjectInterface $proxy, string $publicProperty)
    {
        unset($proxy->$publicProperty);

        self::assertTrue($proxy->isProxyInitialized());
        self::assertTrue(isset($instance->$publicProperty));
        self::assertFalse(isset($proxy->$publicProperty));
    }

    /**
     * Verifies that accessing a public property containing an array behaves like in a normal context
     */
    public function testCanWriteToArrayKeysInPublicProperty()
    {
        $instance    = new ClassWithPublicArrayProperty();
        $className   = get_class($instance);
        $initializer = $this->createInitializer($className, $instance);
        $proxyName   = $this->generateProxy($className);
        /* @var $proxy ClassWithPublicArrayProperty */
        $proxy       = $proxyName::staticProxyConstructor($initializer);

        $proxy->arrayProperty['foo'] = 'bar';

        self::assertSame('bar', $proxy->arrayProperty['foo']);

        $proxy->arrayProperty = ['tab' => 'taz'];

        self::assertSame(['tab' => 'taz'], $proxy->arrayProperty);
    }

    /**
     * Verifies that public properties retrieved via `__get` don't get modified in the object itself
     */
    public function testWillNotModifyRetrievedPublicProperties()
    {
        $instance    = new ClassWithPublicProperties();
        $className   = get_class($instance);
        $initializer = $this->createInitializer($className, $instance);
        $proxyName   = $this->generateProxy($className);
        /* @var $proxy ClassWithPublicProperties */
        $proxy       = $proxyName::staticProxyConstructor($initializer);
        $variable    = $proxy->property0;

        self::assertSame('property0', $variable);

        $variable = 'foo';

        self::assertSame('property0', $proxy->property0);
        self::assertSame('foo', $variable);
    }

    /**
     * Verifies that public properties references retrieved via `__get` modify in the object state
     */
    public function testWillModifyByRefRetrievedPublicProperties()
    {
        $instance    = new ClassWithPublicProperties();
        $className   = get_class($instance);
        $initializer = $this->createInitializer($className, $instance);
        $proxyName   = $this->generateProxy($className);
        /* @var $proxy ClassWithPublicProperties */
        $proxy       = $proxyName::staticProxyConstructor($initializer);
        $variable    = & $proxy->property0;

        self::assertSame('property0', $variable);

        $variable = 'foo';

        self::assertSame('foo', $proxy->property0);
        self::assertSame('foo', $variable);
    }

    public function testKeepsInitializerWhenNotOverwitten()
    {
        $instance    = new BaseClass();
        $proxyName   = $this->generateProxy(get_class($instance));
        $initializer = function () {
        };
        /* @var $proxy GhostObjectInterface */
        $proxy       = $proxyName::staticProxyConstructor($initializer);

        $proxy->initializeProxy();

        self::assertSame($initializer, $proxy->getProxyInitializer());
    }

    /**
     * Verifies that public properties are not being initialized multiple times
     */
    public function testKeepsInitializedPublicProperties()
    {
        $instance    = new BaseClass();
        $proxyName   = $this->generateProxy(get_class($instance));
        $initializer = function (BaseClass $proxy, string $method, $parameters, & $initializer) {
            $initializer           = null;
            $proxy->publicProperty = 'newValue';
        };
        /* @var $proxy GhostObjectInterface|BaseClass */
        $proxy       = $proxyName::staticProxyConstructor($initializer);

        $proxy->initializeProxy();
        self::assertSame('newValue', $proxy->publicProperty);

        $proxy->publicProperty = 'otherValue';

        $proxy->initializeProxy();

        self::assertSame('otherValue', $proxy->publicProperty);
    }

    /**
     * Verifies that properties' default values are preserved
     */
    public function testPublicPropertyDefaultWillBePreserved()
    {
        $instance    = new ClassWithPublicProperties();
        $proxyName   = $this->generateProxy(get_class($instance));
        /* @var $proxy ClassWithPublicProperties */
        $proxy       = $proxyName::staticProxyConstructor(function () {
        });

        self::assertSame('property0', $proxy->property0);
    }

    /**
     * Verifies that protected properties' default values are preserved
     */
    public function testProtectedPropertyDefaultWillBePreserved()
    {
        $instance    = new ClassWithProtectedProperties();
        $proxyName   = $this->generateProxy(get_class($instance));
        /* @var $proxy ClassWithProtectedProperties */
        $proxy       = $proxyName::staticProxyConstructor(function () {
        });

        // Check protected property via reflection
        $reflectionProperty = new ReflectionProperty($instance, 'property0');
        $reflectionProperty->setAccessible(true);

        self::assertSame('property0', $reflectionProperty->getValue($proxy));
    }

    /**
     * Verifies that private properties' default values are preserved
     */
    public function testPrivatePropertyDefaultWillBePreserved()
    {
        $instance  = new ClassWithPrivateProperties();
        $proxyName = $this->generateProxy(get_class($instance));
        /* @var $proxy ClassWithPrivateProperties */
        $proxy     = $proxyName::staticProxyConstructor(function () {
        });

        // Check protected property via reflection
        $reflectionProperty = new ReflectionProperty($instance, 'property0');
        $reflectionProperty->setAccessible(true);

        self::assertSame('property0', $reflectionProperty->getValue($proxy));
    }

    /**
     * @group 159
     * @group 192
     */
    public function testMultiLevelPrivatePropertiesDefaultsWillBePreserved()
    {
        $instance  = new ClassWithCollidingPrivateInheritedProperties();
        $proxyName = $this->generateProxy(ClassWithCollidingPrivateInheritedProperties::class);
        /* @var $proxy ClassWithPrivateProperties */
        $proxy     = $proxyName::staticProxyConstructor(function () {
        });

        $childProperty  = new ReflectionProperty($instance, 'property0');
        $parentProperty = new ReflectionProperty(get_parent_class($instance), 'property0');

        $childProperty->setAccessible(true);
        $parentProperty->setAccessible(true);

        self::assertSame('childClassProperty0', $childProperty->getValue($proxy));
        self::assertSame('property0', $parentProperty->getValue($proxy));
    }

    /**
     * @group 159
     * @group 192
     */
    public function testMultiLevelPrivatePropertiesByRefInitialization()
    {
        $class     = ClassWithCollidingPrivateInheritedProperties::class;
        $proxyName = $this->generateProxy($class);
        /* @var $proxy ClassWithPrivateProperties */
        $proxy     = $proxyName::staticProxyConstructor(
            function ($proxy, $method, $params, & $initializer, array $properties) use ($class) {
                $initializer = null;
                $properties["\0" . $class . "\0property0"] = 'foo';
                $properties["\0" . get_parent_class($class) . "\0property0"] = 'bar';
            }
        );

        $childProperty  = new ReflectionProperty($class, 'property0');
        $parentProperty = new ReflectionProperty(get_parent_class($class), 'property0');

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
    public function testGetPropertyFromDifferentProxyInstances()
    {
        $class     = ClassWithCollidingPrivateInheritedProperties::class;
        $proxyName = $this->generateProxy($class);

        /* @var $proxy1 ClassWithPrivateProperties */
        $proxy1    = $proxyName::staticProxyConstructor(
            function ($proxy, $method, $params, & $initializer, array $properties) use ($class) {
                $initializer = null;
                $properties["\0" . $class . "\0property0"] = 'foo';
                $properties["\0" . get_parent_class($class) . "\0property0"] = 'bar';
            }
        );
        /* @var $proxy2 ClassWithPrivateProperties */
        $proxy2    = $proxyName::staticProxyConstructor(
            function ($proxy, $method, $params, & $initializer, array $properties) use ($class) {
                $initializer = null;
                $properties["\0" . $class . "\0property0"] = 'baz';
                $properties["\0" . get_parent_class($class) . "\0property0"] = 'tab';
            }
        );

        $childProperty  = new ReflectionProperty($class, 'property0');
        $parentProperty = new ReflectionProperty(get_parent_class($class), 'property0');

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
    public function testSetPrivatePropertyOnDifferentProxyInstances()
    {
        $class     = ClassWithMixedPropertiesAndAccessorMethods::class;
        $proxyName = $this->generateProxy($class);

        /* @var $proxy1 ClassWithMixedPropertiesAndAccessorMethods */
        $proxy1    = $proxyName::staticProxyConstructor(
            function ($proxy, $method, $params, & $initializer) {
                $initializer = null;
            }
        );
        /* @var $proxy2 ClassWithMixedPropertiesAndAccessorMethods */
        $proxy2    = $proxyName::staticProxyConstructor(
            function ($proxy, $method, $params, & $initializer) {
                $initializer = null;
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
    public function testIssetPrivatePropertyOnDifferentProxyInstances()
    {
        $class     = ClassWithMixedPropertiesAndAccessorMethods::class;
        $proxyName = $this->generateProxy($class);

        /* @var $proxy1 ClassWithMixedPropertiesAndAccessorMethods */
        $proxy1    = $proxyName::staticProxyConstructor(
            function ($proxy, $method, $params, & $initializer) {
                $initializer = null;
            }
        );
        /* @var $proxy2 ClassWithMixedPropertiesAndAccessorMethods */
        $proxy2    = $proxyName::staticProxyConstructor(
            function ($proxy, $method, $params, & $initializer, array $properties) use ($class) {
                $initializer = null;
                $properties["\0" . $class . "\0privateProperty"] = null;
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
    public function testUnsetPrivatePropertyOnDifferentProxyInstances()
    {
        $class     = ClassWithMixedPropertiesAndAccessorMethods::class;
        $proxyName = $this->generateProxy($class);

        /* @var $proxy1 ClassWithMixedPropertiesAndAccessorMethods */
        $proxy1    = $proxyName::staticProxyConstructor(
            function ($proxy, $method, $params, & $initializer) {
                $initializer = null;
            }
        );
        /* @var $proxy2 ClassWithMixedPropertiesAndAccessorMethods */
        $proxy2    = $proxyName::staticProxyConstructor(
            function ($proxy, $method, $params, & $initializer) {
                $initializer = null;
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
    public function testIssetPrivateAndProtectedPropertiesDoesCheckAgainstBooleanFalse()
    {
        $class     = ClassWithMixedPropertiesAndAccessorMethods::class;
        $proxyName = $this->generateProxy($class);

        /* @var $proxy1 ClassWithMixedPropertiesAndAccessorMethods */
        $proxy1    = $proxyName::staticProxyConstructor(
            function ($proxy, $method, $params, & $initializer, array $properties) use ($class) {
                $initializer = null;
                $properties['publicProperty'] = false;
                $properties["\0*\0protectedProperty"] = false;
                $properties["\0" . $class . "\0privateProperty"] = false;
            }
        );
        /* @var $proxy2 ClassWithMixedPropertiesAndAccessorMethods */
        $proxy2    = $proxyName::staticProxyConstructor(
            function ($proxy, $method, $params, & $initializer, array $properties) use ($class) {
                $initializer = null;
                $properties['publicProperty'] = null;
                $properties["\0*\0protectedProperty"] = null;
                $properties["\0" . $class . "\0privateProperty"] = null;
            }
        );

        self::assertTrue($proxy1->has('protectedProperty'));
        self::assertTrue($proxy1->has('publicProperty'));
        self::assertTrue($proxy1->has('privateProperty'));

        self::assertFalse($proxy2->has('protectedProperty'));
        self::assertFalse($proxy2->has('publicProperty'));
        self::assertFalse($proxy2->has('privateProperty'));
    }

    public function testByRefInitialization()
    {
        $proxyName = $this->generateProxy(ClassWithMixedProperties::class);
        /* @var $proxy ClassWithPrivateProperties */
        $proxy     = $proxyName::staticProxyConstructor(
            function ($proxy, $method, $params, & $initializer, array $properties) {
                $initializer = null;
                $properties["\0" . ClassWithMixedProperties::class . "\0privateProperty0"] = 'private0';
                $properties["\0" . ClassWithMixedProperties::class . "\0privateProperty1"] = 'private1';
                $properties["\0" . ClassWithMixedProperties::class . "\0privateProperty2"] = 'private2';
                $properties["\0*\0protectedProperty0"] = 'protected0';
                $properties["\0*\0protectedProperty1"] = 'protected1';
                $properties["\0*\0protectedProperty2"] = 'protected2';
                $properties['publicProperty0'] = 'public0';
                $properties['publicProperty1'] = 'public1';
                $properties['publicProperty2'] = 'public2';
            }
        );

        $reflectionClass = new ReflectionClass(ClassWithMixedProperties::class);

        foreach (Properties::fromReflectionClass($reflectionClass)->getInstanceProperties() as $property) {
            $property->setAccessible(true);

            self::assertSame(str_replace('Property', '', $property->getName()), $property->getValue($proxy));
        }
    }

    /**
     * @group 115
     * @group 175
     */
    public function testWillBehaveLikeObjectWithNormalConstructor()
    {
        $instance = new ClassWithCounterConstructor(10);

        self::assertSame(10, $instance->amount, 'Verifying that test asset works as expected');
        self::assertSame(10, $instance->getAmount(), 'Verifying that test asset works as expected');
        $instance->__construct(3);
        self::assertSame(13, $instance->amount, 'Verifying that test asset works as expected');
        self::assertSame(13, $instance->getAmount(), 'Verifying that test asset works as expected');

        $proxyName = $this->generateProxy(get_class($instance));

        /* @var $proxy ClassWithCounterConstructor */
        $proxy = new $proxyName(15);

        self::assertSame(15, $proxy->amount, 'Verifying that the proxy constructor works as expected');
        self::assertSame(15, $proxy->getAmount(), 'Verifying that the proxy constructor works as expected');
        $proxy->__construct(5);
        self::assertSame(20, $proxy->amount, 'Verifying that the proxy constructor works as expected');
        self::assertSame(20, $proxy->getAmount(), 'Verifying that the proxy constructor works as expected');
    }

    public function testInitializeProxyWillReturnTrueOnSuccessfulInitialization()
    {
        $proxyName = $this->generateProxy(ClassWithMixedProperties::class);

        /* @var $proxy GhostObjectInterface */
        $proxy = $proxyName::staticProxyConstructor($this->createInitializer(
            ClassWithMixedProperties::class,
            new ClassWithMixedProperties()
        ));

        self::assertTrue($proxy->initializeProxy());
        self::assertTrue($proxy->isProxyInitialized());
        self::assertFalse($proxy->initializeProxy());
    }

    /**
     * Generates a proxy for the given class name, and retrieves its class name
     *
     * @param string  $parentClassName
     * @param mixed[] $proxyOptions
     *
     * @return string
     */
    private function generateProxy(string $parentClassName, array $proxyOptions = []) : string
    {
        $generatedClassName = __NAMESPACE__ . '\\' . UniqueIdentifierGenerator::getIdentifier('Foo');
        $generatedClass     = new ClassGenerator($generatedClassName);

        (new LazyLoadingGhostGenerator())->generate(
            new ReflectionClass($parentClassName),
            $generatedClass,
            $proxyOptions
        );
        (new EvaluatingGeneratorStrategy())->generate($generatedClass);

        return $generatedClassName;
    }

    /**
     * @param string $className
     * @param object $realInstance
     * @param Mock   $initializerMatcher
     *
     * @return callable
     */
    private function createInitializer(string $className, $realInstance, Mock $initializerMatcher = null) : callable
    {
        /* @var $initializerMatcher callable|Mock */
        if (! $initializerMatcher) {
            $initializerMatcher = $this->getMockBuilder(stdClass::class)->setMethods(['__invoke'])->getMock();

            $initializerMatcher
                ->expects(self::once())
                ->method('__invoke')
                ->with(self::logicalAnd(
                    self::isInstanceOf(GhostObjectInterface::class),
                    self::isInstanceOf($className)
                ));
        }

        return function (
            GhostObjectInterface $proxy,
            $method,
            $params,
            & $initializer
        ) use (
            $initializerMatcher,
            $realInstance
        ) : bool {
            $initializer     = null;
            $reflectionClass = new ReflectionClass($realInstance);

            foreach (Properties::fromReflectionClass($reflectionClass)->getInstanceProperties() as $property) {
                $property->setAccessible(true);
                $property->setValue($proxy, $property->getValue($realInstance));
            }

            $initializerMatcher($proxy, $method, $params);

            return true;
        };
    }

    /**
     * Generates a list of object | invoked method | parameters | expected result
     *
     * @return array
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
                'publicMethodDefault'
            ],
            [
                BaseClass::class,
                new BaseClass(),
                'publicTypeHintedMethod',
                [new stdClass()],
                'publicTypeHintedMethodDefault'
            ],
            [
                BaseClass::class,
                new BaseClass(),
                'publicByReferenceMethod',
                [],
                'publicByReferenceMethodDefault'
            ],
            [
                ClassWithSelfHint::class,
                new ClassWithSelfHint(),
                'selfHintMethod',
                ['parameter' => $selfHintParam],
                $selfHintParam
            ],
            [
                ClassWithParentHint::class,
                new ClassWithParentHint(),
                'parentHintMethod',
                ['parameter' => $empty],
                $empty
            ],
            [
                ClassWithAbstractPublicMethod::class,
                new EmptyClass(), // EmptyClass just used to not make reflection explode when synchronizing properties
                'publicAbstractMethod',
                [],
                null
            ],
            [
                ClassWithMethodWithByRefVariadicFunction::class,
                new ClassWithMethodWithByRefVariadicFunction(),
                'tuz',
                ['Ocramius', 'Malukenho'],
                ['Ocramius', 'changed']
            ],
        ];
    }

    /**
     * Generates a list of object | invoked method | parameters | expected result for methods that cause lazy-loading
     * of a ghost object
     *
     * @return array
     */
    public function getProxyInitializingMethods() : array
    {
        return [
            [
                BaseClass::class,
                new BaseClass(),
                'publicPropertyGetter',
                [],
                'publicPropertyDefault'
            ],
            [
                BaseClass::class,
                new BaseClass(),
                'protectedPropertyGetter',
                [],
                'protectedPropertyDefault'
            ],
            [
                BaseClass::class,
                new BaseClass(),
                'privatePropertyGetter',
                [],
                'privatePropertyDefault'
            ],
            [
                ClassWithMethodWithVariadicFunction::class,
                new ClassWithMethodWithVariadicFunction(),
                'foo',
                ['Ocramius', 'Malukenho'],
                null
            ],
        ];
    }

    /**
     * Generates a list of object | invoked method | parameters | expected result for methods DON'T cause lazy-loading
     *
     * @return array
     */
    public function getProxyNonInitializingMethods() : array
    {
        return $this->getProxyMethods();
    }

    /**
     * Generates proxies and instances with a public property to feed to the property accessor methods
     *
     * @return array
     */
    public function getPropertyAccessProxies() : array
    {
        $instance1 = new BaseClass();
        $proxyName1 = $this->generateProxy(get_class($instance1));
        $instance2 = new BaseClass();
        $proxyName2 = $this->generateProxy(get_class($instance2));

        return [
            [
                $instance1,
                new $proxyName1($this->createInitializer(BaseClass::class, $instance1)),
                'publicProperty',
                'publicPropertyDefault',
            ],
            [
                $instance2,
                unserialize(
                    serialize(new $proxyName2($this->createInitializer(BaseClass::class, $instance2)))
                ),
                'publicProperty',
                'publicPropertyDefault',
            ],
        ];
    }

    /**
     * @dataProvider skipPropertiesFixture
     *
     * @param string  $className
     * @param string  $propertyClass
     * @param string  $propertyName
     * @param mixed   $expected
     * @param mixed[] $proxyOptions
     */
    public function testInitializationIsSkippedForSkippedProperties(
        string $className,
        string $propertyClass,
        string $propertyName,
        array $proxyOptions,
        $expected
    ) {
        $proxy       = $this->generateProxy($className, $proxyOptions);
        $ghostObject = $proxy::staticProxyConstructor(function () use ($propertyName) {
            self::fail(sprintf('The Property "%s" was not expected to be lazy-loaded', $propertyName));
        });

        $property = new ReflectionProperty($propertyClass, $propertyName);
        $property->setAccessible(true);

        self::assertSame($expected, $property->getValue($ghostObject));
    }

    /**
     * @dataProvider skipPropertiesFixture
     *
     * @param string  $className
     * @param string  $propertyClass
     * @param string  $propertyName
     * @param mixed[] $proxyOptions
     */
    public function testSkippedPropertiesAreNotOverwrittenOnInitialization(
        string $className,
        string $propertyClass,
        string $propertyName,
        array $proxyOptions
    ) {
        $proxyName   = $this->generateProxy($className, $proxyOptions);
        /* @var $ghostObject GhostObjectInterface */
        $ghostObject = $proxyName::staticProxyConstructor(
            function ($proxy, string $method, $params, & $initializer) : bool {
                $initializer = null;

                return true;
            }
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
    public function testWillForwardVariadicByRefArguments()
    {
        $proxyName   = $this->generateProxy(ClassWithMethodWithByRefVariadicFunction::class);
        /* @var $object ClassWithMethodWithByRefVariadicFunction */
        $object = $proxyName::staticProxyConstructor(function ($proxy, string $method, $params, & $initializer) : bool {
            $initializer = null;

            return true;
        });

        $parameters = ['a', 'b', 'c'];

        // first, testing normal variadic behavior (verifying we didn't screw up in the test asset)
        self::assertSame(['a', 'changed', 'c'], (new ClassWithMethodWithByRefVariadicFunction())->tuz(...$parameters));
        self::assertSame(['a', 'changed', 'c'], $object->tuz(...$parameters));
        self::assertSame(['a', 'changed', 'c'], $parameters, 'by-ref variadic parameter was changed');
    }

    /**
     * @group 265
     */
    public function testWillForwardDynamicArguments()
    {
        $proxyName   = $this->generateProxy(ClassWithDynamicArgumentsMethod::class);
        /* @var $object ClassWithDynamicArgumentsMethod */
        $object = $proxyName::staticProxyConstructor(function () {
        });

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
                    'skippedProperties' => ['property9']
                ],
                'property9',
            ],
            [
                ClassWithProtectedProperties::class,
                ClassWithProtectedProperties::class,
                'property9',
                [
                    'skippedProperties' => ["\0*\0property9"]
                ],
                'property9',
            ],
            [
                ClassWithPrivateProperties::class,
                ClassWithPrivateProperties::class,
                'property9',
                [
                    'skippedProperties' => [
                        "\0ProxyManagerTestAsset\\ClassWithPrivateProperties\0property9"
                    ]
                ],
                'property9',
            ],
            [
                ClassWithCollidingPrivateInheritedProperties::class,
                ClassWithCollidingPrivateInheritedProperties::class,
                'property0',
                [
                    'skippedProperties' => [
                        "\0ProxyManagerTestAsset\\ClassWithCollidingPrivateInheritedProperties\0property0"
                    ]
                ],
                'childClassProperty0',
            ],
            [
                ClassWithCollidingPrivateInheritedProperties::class,
                ClassWithPrivateProperties::class,
                'property0',
                [
                    'skippedProperties' => [
                        "\0ProxyManagerTestAsset\\ClassWithPrivateProperties\0property0"
                    ]
                ],
                'property0',
            ],
        ];
    }

    /**
     * @group 276
     *
     * @dataProvider getMethodsThatAccessPropertiesOnOtherObjectsInTheSameScope
     *
     * @param object $callerObject
     * @param string $method
     * @param string $propertyIndex
     * @param string $expectedValue
     */
    public function testWillLazyLoadMembersOfOtherProxiesWithTheSamePrivateScope(
        $callerObject,
        string $method,
        string $propertyIndex,
        string $expectedValue
    ) {
        $proxyName = $this->generateProxy(get_class($callerObject));
        /* @var $proxy OtherObjectAccessClass|LazyLoadingInterface */
        $proxy = $proxyName::staticProxyConstructor(
            function ($proxy, $method, $params, & $initializer, array $props) use ($propertyIndex, $expectedValue) {
                $initializer = null;

                $props[$propertyIndex] = $expectedValue;
            }
        );

        /* @var $accessor callable */
        $accessor = [$callerObject, $method];

        self::assertFalse($proxy->isProxyInitialized());
        self::assertSame($expectedValue, $accessor($proxy));
        self::assertTrue($proxy->isProxyInitialized());
    }

    /**
     * @group 276
     *
     * @dataProvider getMethodsThatAccessPropertiesOnOtherObjectsInTheSameScope
     *
     * @param object $callerObject
     * @param string $method
     * @param string $propertyIndex
     * @param string $expectedValue
     */
    public function testWillAccessMembersOfOtherDeSerializedProxiesWithTheSamePrivateScope(
        $callerObject,
        string $method,
        string $propertyIndex,
        string $expectedValue
    ) {
        $proxyName = $this->generateProxy(get_class($callerObject));
        /* @var $proxy OtherObjectAccessClass|LazyLoadingInterface */
        $proxy = unserialize(serialize($proxyName::staticProxyConstructor(
            function ($proxy, $method, $params, & $initializer, array $props) use ($propertyIndex, $expectedValue) {
                $initializer = null;

                $props[$propertyIndex] = $expectedValue;
            }
        )));

        /* @var $accessor callable */
        $accessor = [$callerObject, $method];

        self::assertTrue($proxy->isProxyInitialized());
        self::assertSame($expectedValue, $accessor($proxy));
    }

    /**
     * @group 276
     *
     * @dataProvider getMethodsThatAccessPropertiesOnOtherObjectsInTheSameScope
     *
     * @param object $callerObject
     * @param string $method
     * @param string $propertyIndex
     * @param string $expectedValue
     */
    public function testWillAccessMembersOfOtherClonedProxiesWithTheSamePrivateScope(
        $callerObject,
        string $method,
        string $propertyIndex,
        string $expectedValue
    ) {
        $proxyName = $this->generateProxy(get_class($callerObject));
        /* @var $proxy OtherObjectAccessClass|LazyLoadingInterface */
        $proxy = clone $proxyName::staticProxyConstructor(
            function ($proxy, $method, $params, & $initializer, array $props) use ($propertyIndex, $expectedValue) {
                $initializer = null;

                $props[$propertyIndex] = $expectedValue;
            }
        );

        /* @var $accessor callable */
        $accessor = [$callerObject, $method];

        self::assertTrue($proxy->isProxyInitialized());
        self::assertSame($expectedValue, $accessor($proxy));
    }

    public function getMethodsThatAccessPropertiesOnOtherObjectsInTheSameScope() : array
    {
        $proxyClass = $this->generateProxy(OtherObjectAccessClass::class);

        return [
            OtherObjectAccessClass::class . '#$privateProperty' => [
                new OtherObjectAccessClass(),
                'getPrivateProperty',
                "\0" . OtherObjectAccessClass::class . "\0privateProperty",
                uniqid('', true),
            ],
            OtherObjectAccessClass::class . '#$protectedProperty' => [
                new OtherObjectAccessClass(),
                'getProtectedProperty',
                "\0*\0protectedProperty",
                uniqid('', true),
            ],
            OtherObjectAccessClass::class . '#$publicProperty' => [
                new OtherObjectAccessClass(),
                'getPublicProperty',
                'publicProperty',
                uniqid('', true),
            ],
            '(proxy) ' . OtherObjectAccessClass::class . '#$privateProperty' => [
                $proxyClass::staticProxyConstructor(function () {
                    self::fail('Should never be initialized, as its values aren\'t accessed');
                }),
                'getPrivateProperty',
                "\0" . OtherObjectAccessClass::class . "\0privateProperty",
                uniqid('', true),
            ],
            '(proxy) ' . OtherObjectAccessClass::class . '#$protectedProperty' => [
                $proxyClass::staticProxyConstructor(function () {
                    self::fail('Should never be initialized, as its values aren\'t accessed');
                }),
                'getProtectedProperty',
                "\0*\0protectedProperty",
                uniqid('', true),
            ],
            '(proxy) ' . OtherObjectAccessClass::class . '#$publicProperty' => [
                $proxyClass::staticProxyConstructor(function () {
                    self::fail('Should never be initialized, as its values aren\'t accessed');
                }),
                'getPublicProperty',
                'publicProperty',
                uniqid('', true),
            ],
        ];
    }

    /**
     * @group 276
     */
    public function testFriendObjectWillNotCauseLazyLoadingOnSkippedProperty()
    {
        $proxyName = $this->generateProxy(
            OtherObjectAccessClass::class,
            [
                'skippedProperties' => [
                    "\0" . OtherObjectAccessClass::class . "\0privateProperty",
                    "\0*\0protectedProperty",
                    'publicProperty'
                ],
            ]
        );

        /* @var $proxy OtherObjectAccessClass|LazyLoadingInterface */
        $proxy = $proxyName::staticProxyConstructor(function () {
            throw new \BadMethodCallException('The proxy should never be initialized, as all properties are skipped');
        });

        $privatePropertyValue   = uniqid('', true);
        $protectedPropertyValue = uniqid('', true);
        $publicPropertyValue    = uniqid('', true);

        $reflectionPrivateProperty = new \ReflectionProperty(OtherObjectAccessClass::class, 'privateProperty');

        $reflectionPrivateProperty->setAccessible(true);
        $reflectionPrivateProperty->setValue($proxy, $privatePropertyValue);

        $reflectionProtectedProperty = new \ReflectionProperty(OtherObjectAccessClass::class, 'protectedProperty');

        $reflectionProtectedProperty->setAccessible(true);
        $reflectionProtectedProperty->setValue($proxy, $protectedPropertyValue);

        $proxy->publicProperty = $publicPropertyValue;

        $friendObject = new OtherObjectAccessClass();

        self::assertSame($privatePropertyValue, $friendObject->getPrivateProperty($proxy));
        self::assertSame($protectedPropertyValue, $friendObject->getProtectedProperty($proxy));
        self::assertSame($publicPropertyValue, $friendObject->getPublicProperty($proxy));
    }

    public function testClonedSkippedPropertiesArePreserved()
    {

        $proxyName = $this->generateProxy(
            BaseClass::class,
            [
                'skippedProperties' => [
                    "\0" . BaseClass::class . "\0privateProperty",
                    "\0*\0protectedProperty",
                    'publicProperty'
                ],
            ]
        );

        /* @var $proxy BaseClass|GhostObjectInterface */
        $proxy = $proxyName::staticProxyConstructor(function ($proxy) {
            $proxy->setProxyInitializer(null);
        });

        $reflectionPrivate   = new \ReflectionProperty(BaseClass::class, 'privateProperty');
        $reflectionProtected = new \ReflectionProperty(BaseClass::class, 'protectedProperty');

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
}
