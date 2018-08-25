<?php

declare(strict_types=1);

namespace ProxyManagerTest\Functional;

use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject as Mock;
use ProxyManager\Generator\ClassGenerator;
use ProxyManager\Generator\Util\UniqueIdentifierGenerator;
use ProxyManager\GeneratorStrategy\EvaluatingGeneratorStrategy;
use ProxyManager\Proxy\LazyLoadingInterface;
use ProxyManager\Proxy\VirtualProxyInterface;
use ProxyManager\ProxyGenerator\LazyLoadingValueHolderGenerator;
use ProxyManagerTestAsset\BaseClass;
use ProxyManagerTestAsset\BaseInterface;
use ProxyManagerTestAsset\ClassWithCounterConstructor;
use ProxyManagerTestAsset\ClassWithDynamicArgumentsMethod;
use ProxyManagerTestAsset\ClassWithMagicMethods;
use ProxyManagerTestAsset\ClassWithMethodWithByRefVariadicFunction;
use ProxyManagerTestAsset\ClassWithMethodWithVariadicFunction;
use ProxyManagerTestAsset\ClassWithParentHint;
use ProxyManagerTestAsset\ClassWithPublicArrayProperty;
use ProxyManagerTestAsset\ClassWithPublicProperties;
use ProxyManagerTestAsset\ClassWithSelfHint;
use ProxyManagerTestAsset\EmptyClass;
use ProxyManagerTestAsset\OtherObjectAccessClass;
use ProxyManagerTestAsset\VoidCounter;
use ReflectionClass;
use stdClass;
use function array_values;
use function get_class;
use function random_int;
use function serialize;
use function ucfirst;
use function uniqid;
use function unserialize;

/**
 * Tests for {@see \ProxyManager\ProxyGenerator\LazyLoadingValueHolderGenerator} produced objects
 *
 * @group Functional
 * @coversNothing
 */
class LazyLoadingValueHolderFunctionalTest extends TestCase
{
    /**
     * @dataProvider getProxyMethods
     *
     * @param mixed[] $params
     * @param mixed   $expectedValue
     */
    public function testMethodCalls(string $className, object $instance, string $method, array $params, $expectedValue) : void
    {
        $proxyName = $this->generateProxy($className);

        /** @var VirtualProxyInterface $proxy */
        $proxy = $proxyName::staticProxyConstructor($this->createInitializer($className, $instance));

        self::assertFalse($proxy->isProxyInitialized());

        /** @var callable $callProxyMethod */
        $callProxyMethod = [$proxy, $method];
        $parameterValues = array_values($params);

        self::assertSame($expectedValue, $callProxyMethod(...$parameterValues));
        self::assertTrue($proxy->isProxyInitialized());
        self::assertSame($instance, $proxy->getWrappedValueHolderValue());
    }

    /**
     * @dataProvider getProxyMethods
     *
     * @param mixed[] $params
     * @param mixed   $expectedValue
     */
    public function testMethodCallsAfterUnSerialization(
        string $className,
        object $instance,
        string $method,
        array $params,
        $expectedValue
    ) : void {
        $proxyName = $this->generateProxy($className);

        /** @var VirtualProxyInterface $proxy */
        $proxy = unserialize(serialize($proxyName::staticProxyConstructor(
            $this->createInitializer($className, $instance)
        )));

        self::assertTrue($proxy->isProxyInitialized());

        /** @var callable $callProxyMethod */
        $callProxyMethod = [$proxy, $method];
        $parameterValues = array_values($params);

        self::assertInternalType('callable', $callProxyMethod);

        self::assertSame($expectedValue, $callProxyMethod(...$parameterValues));
        self::assertEquals($instance, $proxy->getWrappedValueHolderValue());
    }

    /**
     * @dataProvider getProxyMethods
     *
     * @param mixed[] $params
     * @param mixed   $expectedValue
     */
    public function testMethodCallsAfterCloning(
        string $className,
        object $instance,
        string $method,
        array $params,
        $expectedValue
    ) : void {
        $proxyName = $this->generateProxy($className);

        /** @var VirtualProxyInterface $proxy */
        $proxy  = $proxyName::staticProxyConstructor($this->createInitializer($className, $instance));
        $cloned = clone $proxy;

        self::assertTrue($cloned->isProxyInitialized());
        self::assertNotSame($proxy->getWrappedValueHolderValue(), $cloned->getWrappedValueHolderValue());

        /** @var callable $callProxyMethod */
        $callProxyMethod = [$cloned, $method];
        $parameterValues = array_values($params);

        self::assertInternalType('callable', $callProxyMethod);

        self::assertSame($expectedValue, $callProxyMethod(...$parameterValues));
        self::assertEquals($instance, $cloned->getWrappedValueHolderValue());
    }

    /**
     * @dataProvider getPropertyAccessProxies
     *
     * @param mixed $propertyValue
     */
    public function testPropertyReadAccess(
        object $instance,
        VirtualProxyInterface $proxy,
        string $publicProperty,
        $propertyValue
    ) : void {
        self::assertSame($propertyValue, $proxy->$publicProperty);
        self::assertTrue($proxy->isProxyInitialized());
        self::assertEquals($instance, $proxy->getWrappedValueHolderValue());
    }

    /**
     * @dataProvider getPropertyAccessProxies
     *
     */
    public function testPropertyWriteAccess(object $instance, VirtualProxyInterface $proxy, string $publicProperty) : void
    {
        $newValue               = uniqid();
        $proxy->$publicProperty = $newValue;

        self::assertTrue($proxy->isProxyInitialized());
        self::assertSame($newValue, $proxy->$publicProperty);
        self::assertSame($newValue, $proxy->getWrappedValueHolderValue()->$publicProperty);
    }

    /**
     * @dataProvider getPropertyAccessProxies
     *
     */
    public function testPropertyExistence(object $instance, VirtualProxyInterface $proxy, string $publicProperty) : void
    {
        self::assertSame(isset($instance->$publicProperty), isset($proxy->$publicProperty));
        self::assertTrue($proxy->isProxyInitialized());
        self::assertEquals($instance, $proxy->getWrappedValueHolderValue());
    }

    /**
     * @dataProvider getPropertyAccessProxies
     *
     */
    public function testPropertyAbsence(object $instance, VirtualProxyInterface $proxy, string $publicProperty) : void
    {
        $instance                  = $proxy->getWrappedValueHolderValue() ?: $instance;
        $instance->$publicProperty = null;
        self::assertFalse(isset($proxy->$publicProperty));
        self::assertTrue($proxy->isProxyInitialized());
    }

    /**
     * @dataProvider getPropertyAccessProxies
     *
     */
    public function testPropertyUnset(object $instance, VirtualProxyInterface $proxy, string $publicProperty) : void
    {
        $instance = $proxy->getWrappedValueHolderValue() ?: $instance;
        unset($proxy->$publicProperty);

        self::assertTrue($proxy->isProxyInitialized());

        self::assertFalse(isset($instance->$publicProperty));
        self::assertFalse(isset($proxy->$publicProperty));
    }

    /**
     * Verifies that accessing a public property containing an array behaves like in a normal context
     */
    public function testCanWriteToArrayKeysInPublicProperty() : void
    {
        $instance    = new ClassWithPublicArrayProperty();
        $className   = get_class($instance);
        $initializer = $this->createInitializer($className, $instance);
        $proxyName   = $this->generateProxy($className);
        /** @var ClassWithPublicArrayProperty $proxy */
        $proxy = $proxyName::staticProxyConstructor($initializer);

        $proxy->arrayProperty['foo'] = 'bar';

        self::assertSame('bar', $proxy->arrayProperty['foo']);

        $proxy->arrayProperty = ['tab' => 'taz'];

        self::assertSame(['tab' => 'taz'], $proxy->arrayProperty);
    }

    /**
     * Verifies that public properties retrieved via `__get` don't get modified in the object itself
     */
    public function testWillNotModifyRetrievedPublicProperties() : void
    {
        $instance    = new ClassWithPublicProperties();
        $className   = get_class($instance);
        $initializer = $this->createInitializer($className, $instance);
        $proxyName   = $this->generateProxy($className);
        /** @var ClassWithPublicProperties $proxy */
        $proxy    = $proxyName::staticProxyConstructor($initializer);
        $variable = $proxy->property0;

        self::assertSame('property0', $variable);

        $variable = 'foo';

        self::assertSame('property0', $proxy->property0);
        self::assertSame('foo', $variable);
    }

    /**
     * Verifies that public properties references retrieved via `__get` modify in the object state
     */
    public function testWillModifyByRefRetrievedPublicProperties() : void
    {
        $instance    = new ClassWithPublicProperties();
        $className   = get_class($instance);
        $initializer = $this->createInitializer($className, $instance);
        $proxyName   = $this->generateProxy($className);
        /** @var ClassWithPublicProperties $proxy */
        $proxy    = $proxyName::staticProxyConstructor($initializer);
        $variable = & $proxy->property0;

        self::assertSame('property0', $variable);

        $variable = 'foo';

        self::assertSame('foo', $proxy->property0);
        self::assertSame('foo', $variable);
    }

    /**
     * @group 16
     *
     * Verifies that initialization of a value holder proxy may happen multiple times
     */
    public function testWillAllowMultipleProxyInitialization() : void
    {
        $proxyClass = $this->generateProxy(BaseClass::class);
        $counter    = 0;

        /** @var BaseClass $proxy */
        $proxy = $proxyClass::staticProxyConstructor(function (& $wrappedInstance) use (& $counter) : void {
            $wrappedInstance = new BaseClass();

            $wrappedInstance->publicProperty = (string) ($counter += 1);
        });

        self::assertSame('1', $proxy->publicProperty);
        self::assertSame('2', $proxy->publicProperty);
        self::assertSame('3', $proxy->publicProperty);
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

        $proxyName = $this->generateProxy(get_class($instance));

        /** @var ClassWithCounterConstructor $proxy */
        $proxy = new $proxyName(15);

        self::assertSame(15, $proxy->amount, 'Verifying that the proxy constructor works as expected');
        self::assertSame(15, $proxy->getAmount(), 'Verifying that the proxy constructor works as expected');
        $proxy->__construct(5);
        self::assertSame(20, $proxy->amount, 'Verifying that the proxy constructor works as expected');
        self::assertSame(20, $proxy->getAmount(), 'Verifying that the proxy constructor works as expected');
    }

    /**
     * @group 265
     */
    public function testWillForwardVariadicByRefArguments() : void
    {
        $proxyName = $this->generateProxy(ClassWithMethodWithByRefVariadicFunction::class);
        /** @var ClassWithMethodWithByRefVariadicFunction $object */
        $object = $proxyName::staticProxyConstructor(function (& $wrappedInstance) : void {
            $wrappedInstance = new ClassWithMethodWithByRefVariadicFunction();
        });

        $parameters = ['a', 'b', 'c'];

        // first, testing normal variadic behavior (verifying we didn't screw up in the test asset)
        self::assertSame(['a', 'changed', 'c'], (new ClassWithMethodWithByRefVariadicFunction())->tuz(...$parameters));
        self::assertSame(['a', 'changed', 'c'], $object->tuz(...$parameters));
        self::assertSame(['a', 'changed', 'c'], $parameters, 'by-ref variadic parameter was changed');
    }

    /**
     * This test documents a known limitation: `func_get_args()` (and similars) don't work in proxied APIs.
     * If you manage to make this test pass, then please do send a patch
     *
     * @group 265
     */
    public function testWillNotForwardDynamicArguments() : void
    {
        $proxyName = $this->generateProxy(ClassWithDynamicArgumentsMethod::class);

        /** @var ClassWithDynamicArgumentsMethod $object */
        $object = $proxyName::staticProxyConstructor(function (& $wrappedInstance) : void {
            $wrappedInstance = new ClassWithDynamicArgumentsMethod();
        });

        self::assertSame(['a', 'b'], (new ClassWithDynamicArgumentsMethod())->dynamicArgumentsMethod('a', 'b'));

        $this->expectException(ExpectationFailedException::class);

        self::assertSame(['a', 'b'], $object->dynamicArgumentsMethod('a', 'b'));
    }

    /**
     * Generates a proxy for the given class name, and retrieves its class name
     */
    private function generateProxy(string $parentClassName) : string
    {
        $generatedClassName = __NAMESPACE__ . '\\' . UniqueIdentifierGenerator::getIdentifier('Foo');
        $generator          = new LazyLoadingValueHolderGenerator();
        $generatedClass     = new ClassGenerator($generatedClassName);
        $strategy           = new EvaluatingGeneratorStrategy();

        $generator->generate(new ReflectionClass($parentClassName), $generatedClass);
        $strategy->generate($generatedClass);

        return $generatedClassName;
    }

    private function createInitializer(string $className, object $realInstance, ?Mock $initializerMatcher = null) : callable
    {
        /** @var callable|Mock $initializerMatcher */
        if (! $initializerMatcher) {
            $initializerMatcher = $this->getMockBuilder(stdClass::class)->setMethods(['__invoke'])->getMock();

            $initializerMatcher
                ->expects(self::once())
                ->method('__invoke')
                ->with(
                    self::logicalAnd(
                        self::isInstanceOf(VirtualProxyInterface::class),
                        self::isInstanceOf($className)
                    ),
                    $realInstance
                );
        }

        return function (
            & $wrappedObject,
            VirtualProxyInterface $proxy,
            $method,
            $params,
            & $initializer
        ) use (
            $initializerMatcher,
            $realInstance
        ) : void {
            $initializer   = null;
            $wrappedObject = $realInstance;

            $initializerMatcher($proxy, $wrappedObject, $method, $params);
        };
    }

    /**
     * Generates a list of object, invoked method, parameters, expected result
     *
     * @return string[][]|object[][]|bool[][]|mixed[][][]
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
                BaseInterface::class,
                new BaseClass(),
                'publicMethod',
                [],
                'publicMethodDefault',
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
                ClassWithMethodWithVariadicFunction::class,
                new ClassWithMethodWithVariadicFunction(),
                'buz',
                ['Ocramius', 'Malukenho'],
                ['Ocramius', 'Malukenho'],
            ],
            [
                ClassWithMethodWithByRefVariadicFunction::class,
                new ClassWithMethodWithByRefVariadicFunction(),
                'tuz',
                ['Ocramius', 'Malukenho'],
                ['Ocramius', 'changed'],
            ],
            [
                ClassWithMagicMethods::class,
                new ClassWithMagicMethods(),
                '__get',
                ['parameterName'],
                'parameterName',
            ],
            [
                ClassWithMagicMethods::class,
                new ClassWithMagicMethods(),
                '__set',
                ['foo', 'bar'],
                ['foo' => 'bar'],
            ],
            [
                ClassWithMagicMethods::class,
                new ClassWithMagicMethods(),
                '__isset',
                ['example'],
                true,
            ],
            [
                ClassWithMagicMethods::class,
                new ClassWithMagicMethods(),
                '__isset',
                [''],
                false,
            ],
            [
                ClassWithMagicMethods::class,
                new ClassWithMagicMethods(),
                '__unset',
                ['example'],
                true,
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
        $instance2  = new BaseClass();
        $proxyName2 = $this->generateProxy(get_class($instance2));

        return [
            [
                $instance1,
                $proxyName1::staticProxyConstructor(
                    $this->createInitializer(BaseClass::class, $instance1)
                ),
                'publicProperty',
                'publicPropertyDefault',
            ],
            [
                $instance2,
                unserialize(serialize($proxyName2::staticProxyConstructor(
                    $this->createInitializer(BaseClass::class, $instance2)
                ))),
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
     */
    public function testWillLazyLoadMembersOfOtherProxiesWithTheSamePrivateScope(
        object $callerObject,
        object $realInstance,
        string $method,
        string $expectedValue
    ) : void {
        $proxyName = $this->generateProxy(get_class($realInstance));
        /** @var OtherObjectAccessClass|LazyLoadingInterface $proxy */
        $proxy = $proxyName::staticProxyConstructor($this->createInitializer(get_class($realInstance), $realInstance));

        /** @var callable $accessor */
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
     */
    public function testWillFetchMembersOfOtherDeSerializedProxiesWithTheSamePrivateScope(
        object $callerObject,
        object $realInstance,
        string $method,
        string $expectedValue
    ) : void {
        $proxyName = $this->generateProxy(get_class($realInstance));
        /** @var OtherObjectAccessClass|LazyLoadingInterface $proxy */
        $proxy = unserialize(serialize(
            $proxyName::staticProxyConstructor($this->createInitializer(get_class($realInstance), $realInstance))
        ));

        /** @var callable $accessor */
        $accessor = [$callerObject, $method];

        self::assertTrue($proxy->isProxyInitialized());
        self::assertSame($expectedValue, $accessor($proxy));
    }

    /**
     * @group 276
     *
     * @dataProvider getMethodsThatAccessPropertiesOnOtherObjectsInTheSameScope
     *
     */
    public function testWillFetchMembersOfOtherClonedProxiesWithTheSamePrivateScope(
        object $callerObject,
        object $realInstance,
        string $method,
        string $expectedValue
    ) : void {
        $proxyName = $this->generateProxy(get_class($realInstance));
        /** @var OtherObjectAccessClass|LazyLoadingInterface $proxy */
        $proxy = clone $proxyName::staticProxyConstructor(
            $this->createInitializer(get_class($realInstance), $realInstance)
        );

        /** @var callable $accessor */
        $accessor = [$callerObject, $method];

        self::assertTrue($proxy->isProxyInitialized());
        self::assertSame($expectedValue, $accessor($proxy));
    }

    /**
     * @group 327
     */
    public function testWillExecuteLogicInAVoidMethod() : void
    {
        $proxyName = $this->generateProxy(VoidCounter::class);
        /** @var VoidCounter $proxy */
        $proxy = $proxyName::staticProxyConstructor($this->createInitializer(VoidCounter::class, new VoidCounter()));

        $increment = random_int(100, 1000);

        $proxy->increment($increment);

        self::assertSame($increment, $proxy->counter);
    }

    public function getMethodsThatAccessPropertiesOnOtherObjectsInTheSameScope() : \Generator
    {
        $proxyClass = $this->generateProxy(OtherObjectAccessClass::class);

        foreach ((new \ReflectionClass(OtherObjectAccessClass::class))->getProperties() as $property) {
            $propertyName  = $property->getName();
            $expectedValue = uniqid('', true);

            // callee is an actual object
            yield OtherObjectAccessClass::class . '#$' . $propertyName => [
                new OtherObjectAccessClass(),
                $this->buildInstanceWithValues(new OtherObjectAccessClass(), [$propertyName => $expectedValue]),
                'get' . ucfirst($propertyName),
                $expectedValue,
            ];

            $expectedValue = uniqid('', true);

            // callee is a proxy (not to be lazy-loaded!)
            yield '(proxy) ' . OtherObjectAccessClass::class . '#$' . $propertyName => [
                $proxyClass::staticProxyConstructor($this->createInitializer(
                    OtherObjectAccessClass::class,
                    new OtherObjectAccessClass()
                )),
                $this->buildInstanceWithValues(new OtherObjectAccessClass(), [$propertyName => $expectedValue]),
                'get' . ucfirst($propertyName),
                $expectedValue,
            ];
        }
    }

    /**
     * @param array $values
     *
     */
    private function buildInstanceWithValues(object $instance, array $values) : object
    {
        foreach ($values as $property => $value) {
            $property = new \ReflectionProperty($instance, $property);

            $property->setAccessible(true);

            $property->setValue($instance, $value);
        }

        return $instance;
    }
}
