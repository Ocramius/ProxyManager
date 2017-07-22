<?php

declare(strict_types=1);

namespace ProxyManagerTest\Functional;

use PHPUnit\Framework\TestCase;
use ProxyManager\Factory\AccessInterceptorScopeLocalizerFactory;
use ProxyManager\Factory\AccessInterceptorValueHolderFactory;
use ProxyManager\Factory\LazyLoadingGhostFactory;
use ProxyManager\Factory\LazyLoadingValueHolderFactory;
use ProxyManager\Proxy\AccessInterceptorInterface;
use ProxyManager\Proxy\GhostObjectInterface;
use ProxyManager\Proxy\ValueHolderInterface;
use ProxyManager\Proxy\VirtualProxyInterface;
use ProxyManagerTestAsset\BaseClass;
use ProxyManagerTestAsset\ClassWithByRefMagicMethods;
use ProxyManagerTestAsset\ClassWithCollidingPrivateInheritedProperties;
use ProxyManagerTestAsset\ClassWithFinalMagicMethods;
use ProxyManagerTestAsset\ClassWithFinalMethods;
use ProxyManagerTestAsset\ClassWithMagicMethods;
use ProxyManagerTestAsset\ClassWithMethodWithByRefVariadicFunction;
use ProxyManagerTestAsset\ClassWithMethodWithVariadicFunction;
use ProxyManagerTestAsset\ClassWithMixedProperties;
use ProxyManagerTestAsset\ClassWithParentHint;
use ProxyManagerTestAsset\ClassWithPrivateProperties;
use ProxyManagerTestAsset\ClassWithProtectedProperties;
use ProxyManagerTestAsset\ClassWithPublicProperties;
use ProxyManagerTestAsset\ClassWithSelfHint;
use ProxyManagerTestAsset\EmptyClass;
use ProxyManagerTestAsset\HydratedObject;
use ProxyManagerTestAsset\IterableTypeHintClass;
use ProxyManagerTestAsset\ObjectTypeHintClass;
use ProxyManagerTestAsset\ReturnTypeHintedClass;
use ProxyManagerTestAsset\ScalarTypeHintedClass;
use ProxyManagerTestAsset\VoidMethodTypeHintedClass;

/**
 * Verifies that proxy factories don't conflict with each other when generating proxies
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 *
 * @link https://github.com/Ocramius/ProxyManager/issues/10
 *
 * @group Functional
 * @group issue-10
 * @coversNothing
 */
class MultipleProxyGenerationTest extends TestCase
{
    /**
     * Verifies that proxies generated from different factories will retain their specific implementation
     * and won't conflict
     *
     * @dataProvider getTestedClasses
     *
     * @param string $className
     */
    public function testCanGenerateMultipleDifferentProxiesForSameClass(string $className) : void
    {
        $ghostProxyFactory                      = new LazyLoadingGhostFactory();
        $virtualProxyFactory                    = new LazyLoadingValueHolderFactory();
        $accessInterceptorFactory               = new AccessInterceptorValueHolderFactory();
        $accessInterceptorScopeLocalizerFactory = new AccessInterceptorScopeLocalizerFactory();
        $initializer                            = function () {
        };

        $generated = [
            $ghostProxyFactory->createProxy($className, $initializer),
            $virtualProxyFactory->createProxy($className, $initializer),
            $accessInterceptorFactory->createProxy(new $className()),
            $accessInterceptorScopeLocalizerFactory->createProxy(new $className()),
        ];

        foreach ($generated as $key => $proxy) {
            self::assertInstanceOf($className, $proxy);

            foreach ($generated as $comparedKey => $comparedProxy) {
                if ($comparedKey === $key) {
                    continue;
                }

                self::assertNotSame(get_class($comparedProxy), get_class($proxy));
            }

            $proxyClass = get_class($proxy);

            self::assertInstanceOf($proxyClass, new $proxyClass, 'Proxy can be instantiated via normal constructor');
        }

        self::assertInstanceOf(GhostObjectInterface::class, $generated[0]);
        self::assertInstanceOf(VirtualProxyInterface::class, $generated[1]);
        self::assertInstanceOf(AccessInterceptorInterface::class, $generated[2]);
        self::assertInstanceOf(ValueHolderInterface::class, $generated[2]);
        self::assertInstanceOf(AccessInterceptorInterface::class, $generated[3]);
    }

    /**
     * @return string[][]
     */
    public function getTestedClasses() : array
    {
        return [
            [BaseClass::class],
            [ClassWithMagicMethods::class],
            [ClassWithFinalMethods::class],
            [ClassWithFinalMagicMethods::class],
            [ClassWithByRefMagicMethods::class],
            [ClassWithMixedProperties::class],
            [ClassWithPrivateProperties::class],
            [ClassWithProtectedProperties::class],
            [ClassWithPublicProperties::class],
            [EmptyClass::class],
            [HydratedObject::class],
            [ClassWithSelfHint::class],
            [ClassWithParentHint::class],
            [ClassWithCollidingPrivateInheritedProperties::class],
            [ClassWithMethodWithVariadicFunction::class],
            [ClassWithMethodWithByRefVariadicFunction::class],
            [ScalarTypeHintedClass::class],
            [IterableTypeHintClass::class],
            [ObjectTypeHintClass::class],
            [ReturnTypeHintedClass::class],
            [VoidMethodTypeHintedClass::class],
        ];
    }
}
