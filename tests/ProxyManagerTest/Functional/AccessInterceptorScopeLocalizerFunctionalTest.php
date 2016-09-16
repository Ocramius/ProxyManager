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

use PHPUnit_Framework_TestCase;
use ProxyManager\Configuration;
use ProxyManager\Exception\UnsupportedProxiedClassException;
use ProxyManager\Factory\AccessInterceptorScopeLocalizerFactory;
use ProxyManager\Generator\ClassGenerator;
use ProxyManager\Generator\Util\UniqueIdentifierGenerator;
use ProxyManager\GeneratorStrategy\EvaluatingGeneratorStrategy;
use ProxyManager\Proxy\AccessInterceptorInterface;
use ProxyManager\ProxyGenerator\AccessInterceptorScopeLocalizerGenerator;
use ProxyManager\ProxyGenerator\Util\Properties;
use ProxyManagerTestAsset\BaseClass;
use ProxyManagerTestAsset\ClassWithCounterConstructor;
use ProxyManagerTestAsset\ClassWithDynamicArgumentsMethod;
use ProxyManagerTestAsset\ClassWithMethodWithByRefVariadicFunction;
use ProxyManagerTestAsset\ClassWithMethodWithVariadicFunction;
use ProxyManagerTestAsset\ClassWithParentHint;
use ProxyManagerTestAsset\ClassWithPublicArrayProperty;
use ProxyManagerTestAsset\ClassWithPublicProperties;
use ProxyManagerTestAsset\ClassWithSelfHint;
use ProxyManagerTestAsset\EmptyClass;
use ReflectionClass;
use stdClass;

/**
 * Tests for {@see \ProxyManager\ProxyGenerator\AccessInterceptorScopeLocalizerGenerator} produced objects
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 *
 * @group Functional
 * @coversNothing
 */
class AccessInterceptorScopeLocalizerFunctionalTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider getProxyMethods
     *
     * @param string  $className
     * @param object  $instance
     * @param string  $method
     * @param mixed[] $params
     * @param mixed   $expectedValue
     */
    public function testMethodCalls(string $className, $instance, string $method, array $params, $expectedValue)
    {
        $proxyName = $this->generateProxy($className);

        /* @var $proxy AccessInterceptorInterface */
        $proxy     = $proxyName::staticProxyConstructor($instance);

        $this->assertProxySynchronized($instance, $proxy);
        self::assertSame($expectedValue, call_user_func_array([$proxy, $method], $params));

        /* @var $listener callable|\PHPUnit_Framework_MockObject_MockObject */
        $listener = $this->getMockBuilder(stdClass::class)->setMethods(['__invoke'])->getMock();
        $listener
            ->expects(self::once())
            ->method('__invoke')
            ->with($proxy, $proxy, $method, $params, false);

        $proxy->setMethodPrefixInterceptor(
            $method,
            function ($proxy, $instance, $method, $params, & $returnEarly) use ($listener) {
                $listener($proxy, $instance, $method, $params, $returnEarly);
            }
        );

        self::assertSame($expectedValue, call_user_func_array([$proxy, $method], $params));

        $random = uniqid('', true);

        $proxy->setMethodPrefixInterceptor(
            $method,
            function ($proxy, $instance, string $method, $params, & $returnEarly) use ($random) : string {
                $returnEarly = true;

                return $random;
            }
        );

        self::assertSame($random, call_user_func_array([$proxy, $method], $params));
        $this->assertProxySynchronized($instance, $proxy);
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
    public function testMethodCallsWithSuffixListener(
        string $className,
        $instance,
        string $method,
        array $params,
        $expectedValue
    ) {
        $proxyName = $this->generateProxy($className);

        /* @var $proxy AccessInterceptorInterface */
        $proxy     = $proxyName::staticProxyConstructor($instance);
        /* @var $listener callable|\PHPUnit_Framework_MockObject_MockObject */
        $listener  = $this->getMockBuilder(stdClass::class)->setMethods(['__invoke'])->getMock();
        $listener
            ->expects(self::once())
            ->method('__invoke')
            ->with($proxy, $proxy, $method, $params, $expectedValue, false);

        $proxy->setMethodSuffixInterceptor(
            $method,
            function ($proxy, $instance, $method, $params, $returnValue, & $returnEarly) use ($listener) {
                $listener($proxy, $instance, $method, $params, $returnValue, $returnEarly);
            }
        );

        self::assertSame($expectedValue, call_user_func_array([$proxy, $method], $params));

        $random = uniqid('', true);

        $proxy->setMethodSuffixInterceptor(
            $method,
            function ($proxy, $instance, $method, $params, $returnValue, & $returnEarly) use ($random) : string {
                $returnEarly = true;

                return $random;
            }
        );

        self::assertSame($random, call_user_func_array([$proxy, $method], $params));
        $this->assertProxySynchronized($instance, $proxy);
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
        /* @var $proxy AccessInterceptorInterface */
        $proxy     = unserialize(serialize($proxyName::staticProxyConstructor($instance)));

        self::assertSame($expectedValue, call_user_func_array([$proxy, $method], $params));
        $this->assertProxySynchronized($instance, $proxy);
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

        /* @var $proxy AccessInterceptorInterface */
        $proxy     = $proxyName::staticProxyConstructor($instance);
        $cloned    = clone $proxy;

        $this->assertProxySynchronized($instance, $proxy);
        self::assertSame($expectedValue, call_user_func_array([$cloned, $method], $params));
        $this->assertProxySynchronized($instance, $proxy);
    }

    /**
     * @dataProvider getPropertyAccessProxies
     *
     * @param object                     $instance
     * @param AccessInterceptorInterface $proxy
     * @param string                     $publicProperty
     * @param mixed                      $propertyValue
     */
    public function testPropertyReadAccess(
        $instance,
        AccessInterceptorInterface $proxy,
        string $publicProperty,
        $propertyValue
    ) {
        self::assertSame($propertyValue, $proxy->$publicProperty);
        $this->assertProxySynchronized($instance, $proxy);
    }

    /**
     * @dataProvider getPropertyAccessProxies
     *
     * @param object                     $instance
     * @param AccessInterceptorInterface $proxy
     * @param string                     $publicProperty
     */
    public function testPropertyWriteAccess($instance, AccessInterceptorInterface $proxy, string $publicProperty)
    {
        $newValue               = uniqid();
        $proxy->$publicProperty = $newValue;

        self::assertSame($newValue, $proxy->$publicProperty);
        $this->assertProxySynchronized($instance, $proxy);
    }

    /**
     * @dataProvider getPropertyAccessProxies
     *
     * @param object                     $instance
     * @param AccessInterceptorInterface $proxy
     * @param string                     $publicProperty
     */
    public function testPropertyExistence($instance, AccessInterceptorInterface $proxy, string $publicProperty)
    {
        self::assertSame(isset($instance->$publicProperty), isset($proxy->$publicProperty));
        $this->assertProxySynchronized($instance, $proxy);

        $instance->$publicProperty = null;
        self::assertFalse(isset($proxy->$publicProperty));
        $this->assertProxySynchronized($instance, $proxy);
    }

    /**
     * @dataProvider getPropertyAccessProxies
     *
     * @param object                     $instance
     * @param AccessInterceptorInterface $proxy
     * @param string                     $publicProperty
     */
    public function testPropertyUnset($instance, AccessInterceptorInterface $proxy, string $publicProperty)
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
    public function testCanWriteToArrayKeysInPublicProperty()
    {
        $instance    = new ClassWithPublicArrayProperty();
        $className   = get_class($instance);
        $proxyName   = $this->generateProxy($className);
        /* @var $proxy ClassWithPublicArrayProperty|AccessInterceptorInterface */
        $proxy       = $proxyName::staticProxyConstructor($instance);

        $proxy->arrayProperty['foo'] = 'bar';

        self::assertSame('bar', $proxy->arrayProperty['foo']);

        $proxy->arrayProperty = ['tab' => 'taz'];

        self::assertSame(['tab' => 'taz'], $proxy->arrayProperty);
        $this->assertProxySynchronized($instance, $proxy);
    }

    /**
     * Verifies that public properties retrieved via `__get` don't get modified in the object state
     */
    public function testWillNotModifyRetrievedPublicProperties()
    {
        $instance    = new ClassWithPublicProperties();
        $className   = get_class($instance);
        $proxyName   = $this->generateProxy($className);
        /* @var $proxy ClassWithPublicProperties|AccessInterceptorInterface */
        $proxy       = $proxyName::staticProxyConstructor($instance);
        $variable    = $proxy->property0;

        self::assertSame('property0', $variable);

        $variable = 'foo';

        self::assertSame('property0', $proxy->property0);
        $this->assertProxySynchronized($instance, $proxy);
        self::assertSame('foo', $variable);
    }

    /**
     * Verifies that public properties references retrieved via `__get` modify in the object state
     */
    public function testWillModifyByRefRetrievedPublicProperties()
    {
        $instance    = new ClassWithPublicProperties();
        $proxyName   = $this->generateProxy(get_class($instance));
        /* @var $proxy ClassWithPublicProperties|AccessInterceptorInterface */
        $proxy       = $proxyName::staticProxyConstructor($instance);
        $variable    = & $proxy->property0;

        self::assertSame('property0', $variable);

        $variable = 'foo';

        self::assertSame('foo', $proxy->property0);
        $this->assertProxySynchronized($instance, $proxy);
        self::assertSame('foo', $variable);
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

    /**
     * Generates a proxy for the given class name, and retrieves its class name
     *
     * @param string $parentClassName
     *
     * @return string
     *
     * @throws UnsupportedProxiedClassException
     */
    private function generateProxy(string $parentClassName) : string
    {
        $generatedClassName = __NAMESPACE__ . '\\' . UniqueIdentifierGenerator::getIdentifier('Foo');
        $generator          = new AccessInterceptorScopeLocalizerGenerator();
        $generatedClass     = new ClassGenerator($generatedClassName);
        $strategy           = new EvaluatingGeneratorStrategy();

        $generator->generate(new ReflectionClass($parentClassName), $generatedClass);
        $strategy->generate($generatedClass);

        return $generatedClassName;
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
                ['param' => new stdClass()],
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
        ];
    }

    /**
     * Generates proxies and instances with a public property to feed to the property accessor methods
     *
     * @return array
     */
    public function getPropertyAccessProxies() : array
    {
        $instance1  = new BaseClass();
        $proxyName1 = $this->generateProxy(get_class($instance1));

        return [
            [
                $instance1,
                $proxyName1::staticProxyConstructor($instance1),
                'publicProperty',
                'publicPropertyDefault',
            ],
        ];
    }

    /**
     * @param object                     $instance
     * @param AccessInterceptorInterface $proxy
     */
    private function assertProxySynchronized($instance, AccessInterceptorInterface $proxy)
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

    public function testWillForwardVariadicArguments()
    {
        $configuration = new Configuration();
        $factory       = new AccessInterceptorScopeLocalizerFactory($configuration);
        $targetObject  = new ClassWithMethodWithVariadicFunction();

        /* @var $object ClassWithMethodWithVariadicFunction */
        $object = $factory->createProxy(
            $targetObject,
            [
                function () : string {
                    return 'Foo Baz';
                },
            ]
        );

        self::assertNull($object->bar);
        self::assertNull($object->baz);

        $object->foo('Ocramius', 'Malukenho', 'Danizord');
        self::assertSame('Ocramius', $object->bar);
        self::assertSame(['Malukenho', 'Danizord'], $object->baz);
    }

    /**
     * @group 265
     */
    public function testWillForwardVariadicByRefArguments()
    {
        $configuration = new Configuration();
        $factory       = new AccessInterceptorScopeLocalizerFactory($configuration);
        $targetObject  = new ClassWithMethodWithByRefVariadicFunction();

        /* @var $object ClassWithMethodWithByRefVariadicFunction */
        $object = $factory->createProxy(
            $targetObject,
            [
                function () : string {
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
    public function testWillNotForwardDynamicArguments()
    {
        /* @var $object ClassWithDynamicArgumentsMethod */
        $object = (new AccessInterceptorScopeLocalizerFactory())
            ->createProxy(
                new ClassWithDynamicArgumentsMethod(),
                [
                    'dynamicArgumentsMethod' => function () : string {
                        return 'Foo Baz';
                    },
                ]
            );

        self::assertSame(['a', 'b'], (new ClassWithDynamicArgumentsMethod())->dynamicArgumentsMethod('a', 'b'));

        $this->expectException(\PHPUnit_Framework_ExpectationFailedException::class);

        self::assertSame(['a', 'b'], $object->dynamicArgumentsMethod('a', 'b'));
    }
}
