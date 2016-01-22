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

use PHPUnit_Framework_TestCase;
use ProxyManager\Factory\AccessInterceptorValueHolderFactory;
use ProxyManager\Generator\ClassGenerator;
use ProxyManager\Generator\Util\UniqueIdentifierGenerator;
use ProxyManager\GeneratorStrategy\EvaluatingGeneratorStrategy;
use ProxyManager\Proxy\AccessInterceptorInterface;
use ProxyManager\ProxyGenerator\AccessInterceptorValueHolderGenerator;
use ProxyManagerTestAsset\BaseClass;
use ProxyManagerTestAsset\BaseInterface;
use ProxyManagerTestAsset\ClassWithCounterConstructor;
use ProxyManagerTestAsset\ClassWithDynamicArgumentsMethod;
use ProxyManagerTestAsset\ClassWithMethodWithByRefVariadicFunction;
use ProxyManagerTestAsset\ClassWithMethodWithVariadicFunction;
use ProxyManagerTestAsset\ClassWithPublicArrayProperty;
use ProxyManagerTestAsset\ClassWithPublicProperties;
use ProxyManagerTestAsset\ClassWithSelfHint;
use ProxyManagerTestAsset\OtherObjectAccessClass;
use ReflectionClass;
use stdClass;

/**
 * Tests for {@see \ProxyManager\ProxyGenerator\LazyLoadingValueHolderGenerator} produced objects
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 *
 * @group Functional
 * @coversNothing
 */
class AccessInterceptorValueHolderFunctionalTest extends PHPUnit_Framework_TestCase
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
    public function testMethodCalls($className, $instance, $method, $params, $expectedValue)
    {
        $proxyName = $this->generateProxy($className);

        /* @var $proxy \ProxyManager\Proxy\AccessInterceptorInterface|\ProxyManager\Proxy\ValueHolderInterface */
        $proxy     = $proxyName::staticProxyConstructor($instance);

        $this->assertSame($instance, $proxy->getWrappedValueHolderValue());
        $this->assertSame($expectedValue, call_user_func_array([$proxy, $method], $params));

        /* @var $listener callable|\PHPUnit_Framework_MockObject_MockObject */
        $listener = $this->getMock(stdClass::class, ['__invoke']);
        $listener
            ->expects($this->once())
            ->method('__invoke')
            ->with($proxy, $instance, $method, $params, false);

        $proxy->setMethodPrefixInterceptor(
            $method,
            function ($proxy, $instance, $method, $params, & $returnEarly) use ($listener) {
                $listener($proxy, $instance, $method, $params, $returnEarly);
            }
        );

        $this->assertSame($expectedValue, call_user_func_array([$proxy, $method], $params));

        $random = uniqid();

        $proxy->setMethodPrefixInterceptor(
            $method,
            function ($proxy, $instance, $method, $params, & $returnEarly) use ($random) {
                $returnEarly = true;

                return $random;
            }
        );

        $this->assertSame($random, call_user_func_array([$proxy, $method], $params));
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
    public function testMethodCallsWithSuffixListener($className, $instance, $method, $params, $expectedValue)
    {
        $proxyName = $this->generateProxy($className);

        /* @var $proxy \ProxyManager\Proxy\AccessInterceptorInterface|\ProxyManager\Proxy\ValueHolderInterface */
        $proxy    = $proxyName::staticProxyConstructor($instance);
        /* @var $listener callable|\PHPUnit_Framework_MockObject_MockObject */
        $listener = $this->getMock(stdClass::class, ['__invoke']);
        $listener
            ->expects($this->once())
            ->method('__invoke')
            ->with($proxy, $instance, $method, $params, $expectedValue, false);

        $proxy->setMethodSuffixInterceptor(
            $method,
            function ($proxy, $instance, $method, $params, $returnValue, & $returnEarly) use ($listener) {
                $listener($proxy, $instance, $method, $params, $returnValue, $returnEarly);
            }
        );

        $this->assertSame($expectedValue, call_user_func_array([$proxy, $method], $params));

        $random = uniqid();

        $proxy->setMethodSuffixInterceptor(
            $method,
            function ($proxy, $instance, $method, $params, $returnValue, & $returnEarly) use ($random) {
                $returnEarly = true;

                return $random;
            }
        );

        $this->assertSame($random, call_user_func_array([$proxy, $method], $params));
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
    public function testMethodCallsAfterUnSerialization($className, $instance, $method, $params, $expectedValue)
    {
        $proxyName = $this->generateProxy($className);
        /* @var $proxy \ProxyManager\Proxy\AccessInterceptorInterface|\ProxyManager\Proxy\ValueHolderInterface */
        $proxy     = unserialize(serialize($proxyName::staticProxyConstructor($instance)));

        $this->assertSame($expectedValue, call_user_func_array([$proxy, $method], $params));
        $this->assertEquals($instance, $proxy->getWrappedValueHolderValue());
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
    public function testMethodCallsAfterCloning($className, $instance, $method, $params, $expectedValue)
    {
        $proxyName = $this->generateProxy($className);

        /* @var $proxy \ProxyManager\Proxy\AccessInterceptorInterface|\ProxyManager\Proxy\ValueHolderInterface */
        $proxy     = $proxyName::staticProxyConstructor($instance);
        $cloned    = clone $proxy;

        $this->assertNotSame($proxy->getWrappedValueHolderValue(), $cloned->getWrappedValueHolderValue());
        $this->assertSame($expectedValue, call_user_func_array([$cloned, $method], $params));
        $this->assertEquals($instance, $cloned->getWrappedValueHolderValue());
    }

    /**
     * @dataProvider getPropertyAccessProxies
     *
     * @param object                                                                                  $instance
     * @param \ProxyManager\Proxy\AccessInterceptorInterface|\ProxyManager\Proxy\ValueHolderInterface $proxy
     * @param string                                                                                  $publicProperty
     * @param mixed                                                                                   $propertyValue
     */
    public function testPropertyReadAccess($instance, $proxy, $publicProperty, $propertyValue)
    {
        $this->assertSame($propertyValue, $proxy->$publicProperty);
        $this->assertEquals($instance, $proxy->getWrappedValueHolderValue());
    }

    /**
     * @dataProvider getPropertyAccessProxies
     *
     * @param object                                                                                  $instance
     * @param \ProxyManager\Proxy\AccessInterceptorInterface|\ProxyManager\Proxy\ValueHolderInterface $proxy
     * @param string                                                                                  $publicProperty
     */
    public function testPropertyWriteAccess($instance, $proxy, $publicProperty)
    {
        $newValue               = uniqid();
        $proxy->$publicProperty = $newValue;

        $this->assertSame($newValue, $proxy->$publicProperty);
        $this->assertSame($newValue, $proxy->getWrappedValueHolderValue()->$publicProperty);
    }

    /**
     * @dataProvider getPropertyAccessProxies
     *
     * @param object                                                                                  $instance
     * @param \ProxyManager\Proxy\AccessInterceptorInterface|\ProxyManager\Proxy\ValueHolderInterface $proxy
     * @param string                                                                                  $publicProperty
     */
    public function testPropertyExistence($instance, $proxy, $publicProperty)
    {
        $this->assertSame(isset($instance->$publicProperty), isset($proxy->$publicProperty));
        $this->assertEquals($instance, $proxy->getWrappedValueHolderValue());

        $proxy->getWrappedValueHolderValue()->$publicProperty = null;
        $this->assertFalse(isset($proxy->$publicProperty));
    }

    /**
     * @dataProvider getPropertyAccessProxies
     *
     * @param object                                                                                  $instance
     * @param \ProxyManager\Proxy\AccessInterceptorInterface|\ProxyManager\Proxy\ValueHolderInterface $proxy
     * @param string                                                                                  $publicProperty
     */
    public function testPropertyUnset($instance, $proxy, $publicProperty)
    {
        $instance = $proxy->getWrappedValueHolderValue() ? $proxy->getWrappedValueHolderValue() : $instance;
        unset($proxy->$publicProperty);

        $this->assertFalse(isset($instance->$publicProperty));
        $this->assertFalse(isset($proxy->$publicProperty));
    }

    /**
     * Verifies that accessing a public property containing an array behaves like in a normal context
     */
    public function testCanWriteToArrayKeysInPublicProperty()
    {
        $instance    = new ClassWithPublicArrayProperty();
        $className   = get_class($instance);
        $proxyName   = $this->generateProxy($className);
        /* @var $proxy ClassWithPublicArrayProperty */
        $proxy       = $proxyName::staticProxyConstructor($instance);

        $proxy->arrayProperty['foo'] = 'bar';

        $this->assertSame('bar', $proxy->arrayProperty['foo']);

        $proxy->arrayProperty = ['tab' => 'taz'];

        $this->assertSame(['tab' => 'taz'], $proxy->arrayProperty);
    }

    /**
     * Verifies that public properties retrieved via `__get` don't get modified in the object state
     */
    public function testWillNotModifyRetrievedPublicProperties()
    {
        $instance    = new ClassWithPublicProperties();
        $className   = get_class($instance);
        $proxyName   = $this->generateProxy($className);
        /* @var $proxy ClassWithPublicProperties */
        $proxy       = $proxyName::staticProxyConstructor($instance);
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
        $proxyName   = $this->generateProxy($className);
        /* @var $proxy ClassWithPublicProperties */
        $proxy       = $proxyName::staticProxyConstructor($instance);
        $variable    = & $proxy->property0;

        $this->assertSame('property0', $variable);

        $variable = 'foo';

        $this->assertSame('foo', $proxy->property0);
        $this->assertSame('foo', $variable);
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

    public function testWillForwardVariadicArguments()
    {
        $factory       = new AccessInterceptorValueHolderFactory();
        $targetObject  = new ClassWithMethodWithVariadicFunction();

        /* @var $object ClassWithMethodWithVariadicFunction */
        $object = $factory->createProxy(
            $targetObject,
            [
                function () {
                    return 'Foo Baz';
                },
            ]
        );

        $this->assertNull($object->bar);
        $this->assertNull($object->baz);

        $object->foo('Ocramius', 'Malukenho', 'Danizord');
        $this->assertSame('Ocramius', $object->bar);
        $this->assertSame(['Malukenho', 'Danizord'], $object->baz);
    }

    /**
     * @group 265
     */
    public function testWillForwardVariadicByRefArguments()
    {
        $factory       = new AccessInterceptorValueHolderFactory();
        $targetObject  = new ClassWithMethodWithByRefVariadicFunction();

        /* @var $object ClassWithMethodWithByRefVariadicFunction */
        $object = $factory->createProxy(
            $targetObject,
            [
                function () {
                    return 'Foo Baz';
                },
            ]
        );

        $arguments = ['Ocramius', 'Malukenho', 'Danizord'];

        self::assertSame(
            ['Ocramius', 'changed', 'Danizord'],
            (new ClassWithMethodWithByRefVariadicFunction())->tuz(...$arguments),
            'Verifying that the implementation of the test asset is correct before proceeding'
        );
        self::assertSame(['Ocramius', 'changed', 'Danizord'], $object->tuz(...$arguments));
        self::assertSame(['Ocramius', 'changed', 'Danizord'], $arguments, 'By-ref arguments were changed');
    }

    /**
     * This test documents a known limitation: `func_get_args()` (and similars) don't work in proxied APIs.
     * If you manage to make this test pass, then please do send a patch
     *
     * @group 265
     */
    public function testWillNotForwardDynamicArguments()
    {
        $proxyName = $this->generateProxy(ClassWithDynamicArgumentsMethod::class);

        /* @var $object ClassWithDynamicArgumentsMethod */
        $object = $proxyName::staticProxyConstructor(new ClassWithDynamicArgumentsMethod());

        self::assertSame(['a', 'b'], (new ClassWithDynamicArgumentsMethod())->dynamicArgumentsMethod('a', 'b'));

        $this->setExpectedException(\PHPUnit_Framework_ExpectationFailedException::class);

        self::assertSame(['a', 'b'], $object->dynamicArgumentsMethod('a', 'b'));
    }

    /**
     * Generates a proxy for the given class name, and retrieves its class name
     *
     * @param string $parentClassName
     *
     * @return string
     */
    private function generateProxy($parentClassName)
    {
        $generatedClassName = __NAMESPACE__ . '\\' . UniqueIdentifierGenerator::getIdentifier('Foo');
        $generator          = new AccessInterceptorValueHolderGenerator();
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
                BaseInterface::class,
                new BaseClass(),
                'publicMethod',
                [],
                'publicMethodDefault'
            ],
            [
                ClassWithSelfHint::class,
                new ClassWithSelfHint(),
                'selfHintMethod',
                ['parameter' => $selfHintParam],
                $selfHintParam
            ],
        ];
    }

    /**
     * Generates proxies and instances with a public property to feed to the property accessor methods
     *
     * @return array
     */
    public function getPropertyAccessProxies()
    {
        $instance1  = new BaseClass();
        $proxyName1 = $this->generateProxy(get_class($instance1));
        $instance2  = new BaseClass();
        $proxyName2 = $this->generateProxy(get_class($instance2));

        return [
            [
                $instance1,
                $proxyName1::staticProxyConstructor($instance1),
                'publicProperty',
                'publicPropertyDefault',
            ],
            [
                $instance2,
                unserialize(serialize($proxyName2::staticProxyConstructor($instance2))),
                'publicProperty',
                'publicPropertyDefault',
            ],
        ];
    }

    /**
     * @group 276
     *
     * @dataProvider getMethodsThatAccessPropertiesOnOtherObjectsInTheSameScope
     *
     * @param object $callerObject
     * @param object $realInstance
     * @param string $method
     * @param string $expectedValue
     * @param string $propertyName
     */
    public function testWillInterceptAccessToPropertiesViaFriendClassAccess(
        $callerObject,
        $realInstance,
        string $method,
        string $expectedValue,
        string $propertyName
    ) {
        $proxyName = $this->generateProxy(get_class($realInstance));
        /* @var $proxy OtherObjectAccessClass|AccessInterceptorInterface */
        $proxy = $proxyName::staticProxyConstructor($realInstance);

        /* @var $listener callable|\PHPUnit_Framework_MockObject_MockObject */
        $listener = $this->getMock(\stdClass::class, ['__invoke']);

        $listener
            ->expects(self::once())
            ->method('__invoke')
            ->with($proxy, $realInstance, '__get', ['name' => $propertyName]);

        $proxy->setMethodPrefixInterceptor(
            '__get',
            function ($proxy, $instance, $method, $params, & $returnEarly) use ($listener) {
                $listener($proxy, $instance, $method, $params, $returnEarly);
            }
        );

        /* @var $accessor callable */
        $accessor = [$callerObject, $method];

        self::assertInternalType('callable', $accessor);
        self::assertSame($expectedValue, $accessor($proxy));
    }

    public function getMethodsThatAccessPropertiesOnOtherObjectsInTheSameScope() : \Generator
    {
        $proxyClass = $this->generateProxy(OtherObjectAccessClass::class);

        foreach ((new \ReflectionClass(OtherObjectAccessClass::class))->getProperties() as $property) {
            if (! $property->isPublic()) {
                // @TODO for now, only public properties seem to work correctly for friend class access
                continue;
            }

            $property->setAccessible(true);

            $propertyName  = $property->getName();
            $realInstance  = new OtherObjectAccessClass();
            $expectedValue = uniqid('', true);

            $property->setValue($realInstance, $expectedValue);

            // callee is an actual object
            yield OtherObjectAccessClass::class . '#$' . $propertyName => [
                new OtherObjectAccessClass(),
                $realInstance,
                'get' . ucfirst($propertyName),
                $expectedValue,
                $propertyName,
            ];

            $realInstance  = new OtherObjectAccessClass();
            $expectedValue = uniqid('', true);

            $property->setValue($realInstance, $expectedValue);

            // callee is a proxy (not to be lazy-loaded!)
            yield '(proxy) ' . OtherObjectAccessClass::class . '#$' . $propertyName => [
                $proxyClass::staticProxyConstructor(new OtherObjectAccessClass()),
                $realInstance,
                'get' . ucfirst($propertyName),
                $expectedValue,
                $propertyName,
            ];
        }
    }
}
