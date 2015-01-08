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

namespace ProxyManagerTest\Functional;

use PHPUnit_Framework_MockObject_MockObject as Mock;
use PHPUnit_Framework_TestCase;
use ProxyManager\Generator\ClassGenerator;
use ProxyManager\Generator\Util\UniqueIdentifierGenerator;
use ProxyManager\GeneratorStrategy\EvaluatingGeneratorStrategy;
use ProxyManager\Proxy\GhostObjectInterface;
use ProxyManager\ProxyGenerator\LazyLoadingGhostGenerator;
use ProxyManager\ProxyGenerator\Util\Properties;
use ProxyManagerTestAsset\BaseClass;
use ProxyManagerTestAsset\ClassWithAbstractPublicMethod;
use ProxyManagerTestAsset\ClassWithCollidingPrivateInheritedProperties;
use ProxyManagerTestAsset\ClassWithCounterConstructor;
use ProxyManagerTestAsset\ClassWithMixedProperties;
use ProxyManagerTestAsset\ClassWithMixedPropertiesAndAccessorMethods;
use ProxyManagerTestAsset\ClassWithPrivateProperties;
use ProxyManagerTestAsset\ClassWithProtectedProperties;
use ProxyManagerTestAsset\ClassWithPublicArrayProperty;
use ProxyManagerTestAsset\ClassWithPublicProperties;
use ProxyManagerTestAsset\ClassWithSelfHint;
use ProxyManagerTestAsset\EmptyClass;
use ReflectionClass;
use ReflectionProperty;

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
    public function testMethodCallsThatLazyLoadTheObject($className, $instance, $method, array $params, $expectedValue)
    {
        $proxyName = $this->generateProxy($className);

        /* @var $proxy GhostObjectInterface */
        $proxy = $proxyName::staticProxyConstructor($this->createInitializer($className, $instance));

        $this->assertFalse($proxy->isProxyInitialized());
        $this->assertSame($expectedValue, call_user_func_array([$proxy, $method], $params));
        $this->assertTrue($proxy->isProxyInitialized());
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
        $className,
        $instance,
        $method,
        array $params,
        $expectedValue
    ) {
        $proxyName         = $this->generateProxy($className);
        $initializeMatcher = $this->getMock('stdClass', ['__invoke']);

        $initializeMatcher->expects($this->never())->method('__invoke'); // should not initialize the proxy

        /* @var $proxy GhostObjectInterface */
        $proxy = $proxyName::staticProxyConstructor(
            $this->createInitializer($className, $instance, $initializeMatcher)
        );

        $this->assertFalse($proxy->isProxyInitialized());
        $this->assertSame($expectedValue, call_user_func_array(array($proxy, $method), $params));
        $this->assertFalse($proxy->isProxyInitialized());
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
    public function testMethodCallsAfterUnSerialization($className, $instance, $method, array $params, $expectedValue)
    {
        $proxyName = $this->generateProxy($className);

        /* @var $proxy GhostObjectInterface */
        $proxy = unserialize(serialize($proxyName::staticProxyConstructor(
            $this->createInitializer($className, $instance)
        )));

        $this->assertTrue($proxy->isProxyInitialized());
        $this->assertSame($expectedValue, call_user_func_array([$proxy, $method], $params));
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
    public function testMethodCallsAfterCloning($className, $instance, $method, array $params, $expectedValue)
    {
        $proxyName = $this->generateProxy($className);

        /* @var $proxy GhostObjectInterface */
        $proxy  = $proxyName::staticProxyConstructor($this->createInitializer($className, $instance));
        $cloned = clone $proxy;

        $this->assertTrue($cloned->isProxyInitialized());
        $this->assertSame($expectedValue, call_user_func_array([$cloned, $method], $params));
    }

    /**
     * @dataProvider getPropertyAccessProxies
     *
     * @param object               $instance
     * @param GhostObjectInterface $proxy
     * @param string               $publicProperty
     * @param mixed                $propertyValue
     */
    public function testPropertyReadAccess($instance, GhostObjectInterface $proxy, $publicProperty, $propertyValue)
    {
        $this->assertSame($propertyValue, $proxy->$publicProperty);
        $this->assertTrue($proxy->isProxyInitialized());
    }

    /**
     * @dataProvider getPropertyAccessProxies
     *
     * @param object               $instance
     * @param GhostObjectInterface $proxy
     * @param string               $publicProperty
     */
    public function testPropertyWriteAccess($instance, GhostObjectInterface $proxy, $publicProperty)
    {
        $newValue               = uniqid();
        $proxy->$publicProperty = $newValue;

        $this->assertTrue($proxy->isProxyInitialized());
        $this->assertSame($newValue, $proxy->$publicProperty);
    }

    /**
     * @dataProvider getPropertyAccessProxies
     *
     * @param object               $instance
     * @param GhostObjectInterface $proxy
     * @param string               $publicProperty
     */
    public function testPropertyExistence($instance, GhostObjectInterface $proxy, $publicProperty)
    {
        $this->assertSame(isset($instance->$publicProperty), isset($proxy->$publicProperty));
        $this->assertTrue($proxy->isProxyInitialized());
    }

    /**
     * @dataProvider getPropertyAccessProxies
     *
     * @param object               $instance
     * @param GhostObjectInterface $proxy
     * @param string               $publicProperty
     */
    public function testPropertyAbsence($instance, GhostObjectInterface $proxy, $publicProperty)
    {
        $proxy->$publicProperty = null;
        $this->assertFalse(isset($proxy->$publicProperty));
        $this->assertTrue($proxy->isProxyInitialized());
    }

    /**
     * @dataProvider getPropertyAccessProxies
     *
     * @param object               $instance
     * @param GhostObjectInterface $proxy
     * @param string               $publicProperty
     */
    public function testPropertyUnset($instance, GhostObjectInterface $proxy, $publicProperty)
    {
        unset($proxy->$publicProperty);

        $this->assertTrue($proxy->isProxyInitialized());
        $this->assertTrue(isset($instance->$publicProperty));
        $this->assertFalse(isset($proxy->$publicProperty));
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

        $this->assertSame('bar', $proxy->arrayProperty['foo']);

        $proxy->arrayProperty = ['tab' => 'taz'];

        $this->assertSame(['tab' => 'taz'], $proxy->arrayProperty);
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

        $this->assertSame('property0', $variable);

        $variable = 'foo';

        $this->assertSame('property0', $proxy->property0);
        $this->assertSame('foo', $variable);
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

        $this->assertSame('property0', $variable);

        $variable = 'foo';

        $this->assertSame('foo', $proxy->property0);
        $this->assertSame('foo', $variable);
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

        $this->assertSame($initializer, $proxy->getProxyInitializer());
    }

    /**
     * Verifies that public properties are not being initialized multiple times
     */
    public function testKeepsInitializedPublicProperties()
    {
        $instance    = new BaseClass();
        $proxyName   = $this->generateProxy(get_class($instance));
        $initializer = function (BaseClass $proxy, $method, $parameters, & $initializer) {
            $initializer           = null;
            $proxy->publicProperty = 'newValue';
        };
        /* @var $proxy GhostObjectInterface|BaseClass */
        $proxy       = $proxyName::staticProxyConstructor($initializer);

        $proxy->initializeProxy();
        $this->assertSame('newValue', $proxy->publicProperty);

        $proxy->publicProperty = 'otherValue';

        $proxy->initializeProxy();

        $this->assertSame('otherValue', $proxy->publicProperty);
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

        $this->assertSame('property0', $proxy->property0);
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

        $this->assertSame('property0', $reflectionProperty->getValue($proxy));
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

        $this->assertSame('property0', $reflectionProperty->getValue($proxy));
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

        $this->assertSame('childClassProperty0', $childProperty->getValue($proxy));
        $this->assertSame('property0', $parentProperty->getValue($proxy));
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

        $this->assertSame('foo', $childProperty->getValue($proxy));
        $this->assertSame('bar', $parentProperty->getValue($proxy));
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

        $this->assertSame('foo', $childProperty->getValue($proxy1));
        $this->assertSame('bar', $parentProperty->getValue($proxy1));

        $this->assertSame('baz', $childProperty->getValue($proxy2));
        $this->assertSame('tab', $parentProperty->getValue($proxy2));
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
            function ($proxy, $method, $params, & $initializer, array $properties) {
                $initializer = null;
            }
        );
        /* @var $proxy2 ClassWithMixedPropertiesAndAccessorMethods */
        $proxy2    = $proxyName::staticProxyConstructor(
            function ($proxy, $method, $params, & $initializer, array $properties) {
                $initializer = null;
            }
        );

        $proxy1->set('privateProperty', 'private1');
        $proxy2->set('privateProperty', 'private2');
        $this->assertSame('private1', $proxy1->get('privateProperty'));
        $this->assertSame('private2', $proxy2->get('privateProperty'));
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
            function ($proxy, $method, $params, & $initializer, array $properties) {
                $initializer = null;
            }
        );
        /* @var $proxy2 ClassWithMixedPropertiesAndAccessorMethods */
        $proxy2    = $proxyName::staticProxyConstructor(
            function ($proxy, $method, $params, & $initializer, array $properties) use ($class) {
                $initializer = null;
                $properties["\0" . $class . "\0" . "privateProperty"] = null;
            }
        );

        $this->assertTrue($proxy1->has('privateProperty'));
        $this->assertFalse($proxy2->has('privateProperty'));
        $this->assertTrue($proxy1->has('privateProperty'));
        $this->assertFalse($proxy2->has('privateProperty'));
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
            function ($proxy, $method, $params, & $initializer, array $properties) {
                $initializer = null;
            }
        );
        /* @var $proxy2 ClassWithMixedPropertiesAndAccessorMethods */
        $proxy2    = $proxyName::staticProxyConstructor(
            function ($proxy, $method, $params, & $initializer, array $properties) {
                $initializer = null;
            }
        );

        $this->assertTrue($proxy1->has('privateProperty'));
        $proxy2->remove('privateProperty');
        $this->assertFalse($proxy2->has('privateProperty'));
        $this->assertTrue($proxy1->has('privateProperty'));
        $proxy1->remove('privateProperty');
        $this->assertFalse($proxy1->has('privateProperty'));
        $this->assertFalse($proxy2->has('privateProperty'));
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
                $properties["publicProperty"] = false;
                $properties["\0*\0" . "protectedProperty"] = false;
                $properties["\0" . $class . "\0" . "privateProperty"] = false;
            }
        );
        /* @var $proxy2 ClassWithMixedPropertiesAndAccessorMethods */
        $proxy2    = $proxyName::staticProxyConstructor(
            function ($proxy, $method, $params, & $initializer, array $properties) use ($class) {
                $initializer = null;
                $properties["publicProperty"] = null;
                $properties["\0*\0" . "protectedProperty"] = null;
                $properties["\0" . $class . "\0" . "privateProperty"] = null;
            }
        );

        $this->assertTrue($proxy1->has('protectedProperty'));
        $this->assertTrue($proxy1->has('publicProperty'));
        $this->assertTrue($proxy1->has('privateProperty'));

        $this->assertFalse($proxy2->has('protectedProperty'));
        $this->assertFalse($proxy2->has('publicProperty'));
        $this->assertFalse($proxy2->has('privateProperty'));
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
                $properties["publicProperty0"] = 'public0';
                $properties["publicProperty1"] = 'public1';
                $properties["publicProperty2"] = 'public2';
            }
        );

        $reflectionClass = new ReflectionClass(ClassWithMixedProperties::class);

        foreach (Properties::fromReflectionClass($reflectionClass)->getInstanceProperties() as $property) {
            $property->setAccessible(true);

            $this->assertSame(str_replace('Property', '', $property->getName()), $property->getValue($proxy));
        }
    }

    /**
     * @group 115
     * @group 175
     */
    public function testWillBehaveLikeObjectWithNormalConstructor()
    {
        $instance = new ClassWithCounterConstructor(10);

        $this->assertSame(10, $instance->amount, 'Verifying that test asset works as expected');
        $this->assertSame(10, $instance->getAmount(), 'Verifying that test asset works as expected');
        $instance->__construct(3);
        $this->assertSame(13, $instance->amount, 'Verifying that test asset works as expected');
        $this->assertSame(13, $instance->getAmount(), 'Verifying that test asset works as expected');

        $proxyName = $this->generateProxy(get_class($instance));

        /* @var $proxy ClassWithCounterConstructor */
        $proxy = new $proxyName(15);

        $this->assertSame(15, $proxy->amount, 'Verifying that the proxy constructor works as expected');
        $this->assertSame(15, $proxy->getAmount(), 'Verifying that the proxy constructor works as expected');
        $proxy->__construct(5);
        $this->assertSame(20, $proxy->amount, 'Verifying that the proxy constructor works as expected');
        $this->assertSame(20, $proxy->getAmount(), 'Verifying that the proxy constructor works as expected');
    }

    public function testInitializeProxyWillReturnTrueOnSuccessfulInitialization()
    {
        $proxyName = $this->generateProxy(ClassWithMixedProperties::class);

        /* @var $proxy GhostObjectInterface */
        $proxy = $proxyName::staticProxyConstructor($this->createInitializer(
            ClassWithMixedProperties::class,
            new ClassWithMixedProperties()
        ));

        $this->assertTrue($proxy->initializeProxy());
        $this->assertTrue($proxy->isProxyInitialized());
        $this->assertFalse($proxy->initializeProxy());
    }

    /**
     * Generates a proxy for the given class name, and retrieves its class name
     *
     * @param string  $parentClassName
     * @param mixed[] $proxyOptions
     *
     * @return string
     */
    private function generateProxy($parentClassName, array $proxyOptions = [])
    {
        $generatedClassName = __NAMESPACE__ . '\\' . UniqueIdentifierGenerator::getIdentifier('Foo');
        $generatedClass     = new ClassGenerator($generatedClassName);

        (new LazyLoadingGhostGenerator())->generate(new ReflectionClass($parentClassName), $generatedClass, $proxyOptions);
        (new EvaluatingGeneratorStrategy())->generate($generatedClass);

        return $generatedClassName;
    }

    /**
     * @param string $className
     * @param object $realInstance
     * @param Mock   $initializerMatcher
     *
     * @return \Closure
     */
    private function createInitializer($className, $realInstance, Mock $initializerMatcher = null)
    {
        if (null === $initializerMatcher) {
            $initializerMatcher = $this->getMock('stdClass', ['__invoke']);

            $initializerMatcher
                ->expects($this->once())
                ->method('__invoke')
                ->with(
                    $this->logicalAnd(
                        $this->isInstanceOf(GhostObjectInterface::class),
                        $this->isInstanceOf($className)
                    )
                );
        }

        /* @var $initializerMatcher callable */
        $initializerMatcher = $initializerMatcher ?: $this->getMock('stdClass', ['__invoke']);

        return function (
            GhostObjectInterface $proxy,
            $method,
            $params,
            & $initializer
        ) use (
            $initializerMatcher,
            $realInstance
        ) {
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
    public function getProxyMethods()
    {
        $selfHintParam = new ClassWithSelfHint();

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
                [new \stdClass()],
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
                ClassWithAbstractPublicMethod::class,
                new EmptyClass(), // EmptyClass just used to not make reflection explode when synchronizing properties
                'publicAbstractMethod',
                [],
                null
            ],
        ];
    }

    /**
     * Generates a list of object | invoked method | parameters | expected result for methods that cause lazy-loading
     * of a ghost object
     *
     * @return array
     */
    public function getProxyInitializingMethods()
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
        ];
    }

    /**
     * Generates a list of object | invoked method | parameters | expected result for methods DON'T cause lazy-loading
     *
     * @return array
     */
    public function getProxyNonInitializingMethods()
    {
        return $this->getProxyMethods();
    }

    /**
     * Generates proxies and instances with a public property to feed to the property accessor methods
     *
     * @return array
     */
    public function getPropertyAccessProxies()
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
        $className,
        $propertyClass,
        $propertyName,
        array $proxyOptions,
        $expected
    ) {
        $proxy       = $this->generateProxy($className, $proxyOptions);
        $ghostObject = $proxy::staticProxyConstructor(function () use ($propertyName) {
            $this->fail(sprintf('The Property "%s" was not expected to be lazy-loaded', $propertyName));
        });

        $property = new ReflectionProperty($propertyClass, $propertyName);
        $property->setAccessible(true);

        $this->assertSame($expected, $property->getValue($ghostObject));
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
        $className,
        $propertyClass,
        $propertyName,
        array $proxyOptions
    ) {
        $proxyName   = $this->generateProxy($className, $proxyOptions);
        /* @var $ghostObject GhostObjectInterface */
        $ghostObject = $proxyName::staticProxyConstructor(function ($proxy, $method, $params, & $initializer) {
            $initializer = null;

            return true;
        });

        $property = new ReflectionProperty($propertyClass, $propertyName);

        $property->setAccessible(true);

        $value = uniqid('', true);

        $property->setValue($ghostObject, $value);

        $this->assertTrue($ghostObject->initializeProxy());

        $this->assertSame(
            $value,
            $property->getValue($ghostObject),
            'Property should not be changed by proxy initialization'
        );
    }

    /**
     * @return mixed[] in order:
     *                  - the class to be proxied
     *                  - the class owning the property to be checked
     *                  - the property name
     *                  - the options to be passed to the generator
     *                  - the expected value of the property
     */
    public function skipPropertiesFixture()
    {
        return [
            [
                ClassWithPublicProperties::class,
                ClassWithPublicProperties::class,
                'property9',
                [
                    'skippedProperties' => ["property9"]
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
}
