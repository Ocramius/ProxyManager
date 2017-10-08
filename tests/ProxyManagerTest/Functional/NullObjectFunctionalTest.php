<?php

declare(strict_types=1);

namespace ProxyManagerTest\Functional;

use PHPUnit\Framework\TestCase;
use ProxyManager\Generator\ClassGenerator;
use ProxyManager\Generator\Util\UniqueIdentifierGenerator;
use ProxyManager\GeneratorStrategy\EvaluatingGeneratorStrategy;
use ProxyManager\Proxy\NullObjectInterface;
use ProxyManager\ProxyGenerator\NullObjectGenerator;
use ProxyManagerTestAsset\BaseClass;
use ProxyManagerTestAsset\BaseInterface;
use ProxyManagerTestAsset\ClassWithMethodWithByRefVariadicFunction;
use ProxyManagerTestAsset\ClassWithMethodWithVariadicFunction;
use ProxyManagerTestAsset\ClassWithParentHint;
use ProxyManagerTestAsset\ClassWithSelfHint;
use ProxyManagerTestAsset\EmptyClass;
use ProxyManagerTestAsset\VoidCounter;
use ReflectionClass;

/**
 * Tests for {@see \ProxyManager\ProxyGenerator\NullObjectGenerator} produced objects
 *
 * @author Vincent Blanchon <blanchon.vincent@gmail.com>
 * @license MIT
 *
 * @group Functional
 * @coversNothing
 */
class NullObjectFunctionalTest extends TestCase
{
    /**
     * @dataProvider getProxyMethods
     *
     * @param string  $className
     * @param string  $method
     * @param mixed[] $params
     */
    public function testMethodCalls(string $className, string $method, array $params) : void
    {
        $proxyName = $this->generateProxy($className);

        /* @var $proxy NullObjectInterface */
        $proxy = $proxyName::staticProxyConstructor();

        $this->assertNullMethodCall($proxy, $method, $params);
    }

    /**
     * @dataProvider getProxyMethods
     *
     * @param string  $className
     * @param string  $method
     * @param mixed[] $params
     */
    public function testMethodCallsAfterUnSerialization(string $className, string $method, array $params) : void
    {
        $proxyName = $this->generateProxy($className);
        /* @var $proxy NullObjectInterface */
        $proxy     = unserialize(serialize($proxyName::staticProxyConstructor()));

        $this->assertNullMethodCall($proxy, $method, $params);
    }

    /**
     * @dataProvider getProxyMethods
     *
     * @param string  $className
     * @param string  $method
     * @param mixed[] $params
     */
    public function testMethodCallsAfterCloning(string $className, string $method, array $params) : void
    {
        $proxyName = $this->generateProxy($className);

        /* @var $proxy NullObjectInterface */
        $proxy     = $proxyName::staticProxyConstructor();

        $this->assertNullMethodCall(clone $proxy, $method, $params);
    }

    /**
     * @dataProvider getPropertyAccessProxies
     *
     * @param NullObjectInterface $proxy
     * @param string              $publicProperty
     */
    public function testPropertyReadAccess(NullObjectInterface $proxy, string $publicProperty) : void
    {
        self::assertNull($proxy->$publicProperty);
    }

    /**
     * @dataProvider getPropertyAccessProxies
     *
     * @param NullObjectInterface $proxy
     * @param string              $publicProperty
     */
    public function testPropertyWriteAccess(NullObjectInterface $proxy, string $publicProperty) : void
    {
        $newValue               = uniqid();
        $proxy->$publicProperty = $newValue;

        self::assertSame($newValue, $proxy->$publicProperty);
    }

    /**
     * @dataProvider getPropertyAccessProxies
     *
     * @param NullObjectInterface $proxy
     * @param string              $publicProperty
     */
    public function testPropertyExistence(NullObjectInterface $proxy, string $publicProperty) : void
    {
        self::assertNull($proxy->$publicProperty);
    }

    /**
     * @dataProvider getPropertyAccessProxies
     *
     * @param NullObjectInterface $proxy
     * @param string              $publicProperty
     */
    public function testPropertyUnset(NullObjectInterface $proxy, string $publicProperty) : void
    {
        unset($proxy->$publicProperty);

        self::assertFalse(isset($proxy->$publicProperty));
    }

    /**
     * Generates a proxy for the given class name, and retrieves its class name
     */
    private function generateProxy(string $parentClassName) : string
    {
        $generatedClassName = __NAMESPACE__ . '\\' . UniqueIdentifierGenerator::getIdentifier('Foo');
        $generator          = new NullObjectGenerator();
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
                'publicMethod',
                [],
                'publicMethodDefault'
            ],
            [
                BaseClass::class,
                'publicTypeHintedMethod',
                ['param' => new \stdClass()],
                'publicTypeHintedMethodDefault'
            ],
            [
                BaseClass::class,
                'publicByReferenceMethod',
                [],
                'publicByReferenceMethodDefault'
            ],
            [
                BaseInterface::class,
                'publicMethod',
                [],
                'publicMethodDefault'
            ],
            [
                ClassWithSelfHint::class,
                'selfHintMethod',
                ['parameter' => $selfHintParam],
                $selfHintParam
            ],
            [
                ClassWithParentHint::class,
                'parentHintMethod',
                ['parameter' => $empty],
                $empty
            ],
            [
                ClassWithMethodWithVariadicFunction::class,
                'buz',
                ['Ocramius', 'Malukenho'],
                null
            ],
            [
                ClassWithMethodWithByRefVariadicFunction::class,
                'tuz',
                ['Ocramius', 'Malukenho'],
                null
            ],
            [
                VoidCounter::class,
                'increment',
                [random_int(10, 1000)],
                null
            ],
        ];
    }

    /**
     * Generates proxies and instances with a public property to feed to the property accessor methods
     */
    public function getPropertyAccessProxies() : array
    {
        $proxyName1 = $this->generateProxy(BaseClass::class);
        $proxyName2 = $this->generateProxy(BaseClass::class);

        return [
            [
                $proxyName1::staticProxyConstructor(),
                'publicProperty',
                'publicPropertyDefault',
            ],
            [
                unserialize(serialize($proxyName2::staticProxyConstructor())),
                'publicProperty',
                'publicPropertyDefault',
            ],
        ];
    }

    /**
     * @param NullObjectInterface $proxy
     * @param string              $methodName
     * @param array               $parameters
     */
    private function assertNullMethodCall(NullObjectInterface $proxy, string $methodName, array $parameters)
    {
        /* @var callable $method */
        $method = [$proxy, $methodName];

        self::assertInternalType('callable', $method);

        $parameterValues = array_values($parameters);

        self::assertNull($method(...$parameterValues));
    }
}
