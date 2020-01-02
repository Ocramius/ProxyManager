<?php

declare(strict_types=1);

namespace ProxyManagerTest\Functional;

use PHPUnit\Framework\TestCase;
use ProxyManager\Factory\AccessInterceptorScopeLocalizerFactory;
use ProxyManager\Factory\AccessInterceptorValueHolderFactory;
use ProxyManager\Factory\LazyLoadingGhostFactory;
use ProxyManager\Factory\LazyLoadingValueHolderFactory;
use ProxyManagerTestAsset\BaseClass;
use ProxyManagerTestAsset\ClassWithByRefMagicMethods;
use ProxyManagerTestAsset\ClassWithCollidingPrivateInheritedProperties;
use ProxyManagerTestAsset\ClassWithFinalMagicMethods;
use ProxyManagerTestAsset\ClassWithFinalMethods;
use ProxyManagerTestAsset\ClassWithMagicMethods;
use ProxyManagerTestAsset\ClassWithMethodWithByRefVariadicFunction;
use ProxyManagerTestAsset\ClassWithMethodWithVariadicFunction;
use ProxyManagerTestAsset\ClassWithMixedProperties;
use ProxyManagerTestAsset\ClassWithMixedReferenceableTypedProperties;
use ProxyManagerTestAsset\ClassWithMixedTypedProperties;
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
use function get_class;

/**
 * Verifies that proxy factories don't conflict with each other when generating proxies
 *
 * @link https://github.com/Ocramius/ProxyManager/issues/10
 *
 * @group Functional
 * @group issue-10
 * @coversNothing
 */
final class MultipleProxyGenerationTest extends TestCase
{
    /**
     * Verifies that proxies generated from different factories will retain their specific implementation
     * and won't conflict
     *
     * @dataProvider getTestedClasses
     */
    public function testCanGenerateMultipleDifferentProxiesForSameClass(object $object) : void
    {
        $ghostProxyFactory                      = new LazyLoadingGhostFactory();
        $virtualProxyFactory                    = new LazyLoadingValueHolderFactory();
        $accessInterceptorFactory               = new AccessInterceptorValueHolderFactory();
        $accessInterceptorScopeLocalizerFactory = new AccessInterceptorScopeLocalizerFactory();
        $className                              = get_class($object);
        $initializer                            = static function () : bool {
            return true;
        };

        $generated = [
            $ghostProxyFactory->createProxy($className, $initializer),
            $virtualProxyFactory->createProxy($className, $initializer),
            $accessInterceptorFactory->createProxy($object),
        ];

        if ($className !== ClassWithMixedTypedProperties::class) {
            $generated[] = $accessInterceptorScopeLocalizerFactory->createProxy($object);
        }

        foreach ($generated as $key => $proxy) {
            self::assertInstanceOf($className, $proxy);

            foreach ($generated as $comparedKey => $comparedProxy) {
                if ($comparedKey === $key) {
                    continue;
                }

                self::assertNotSame(get_class($comparedProxy), get_class($proxy));
            }

            $proxyClass = get_class($proxy);

            /**
             * @psalm-suppress InvalidStringClass
             * @psalm-suppress MixedMethodCall
             */
            self::assertInstanceOf($proxyClass, new $proxyClass(), 'Proxy can be instantiated via normal constructor');
        }
    }

    /**
     * @return object[][]
     */
    public function getTestedClasses() : array
    {
        return [
            [new BaseClass()],
            [new ClassWithMagicMethods()],
            [new ClassWithFinalMethods()],
            [new ClassWithFinalMagicMethods()],
            [new ClassWithByRefMagicMethods()],
            [new ClassWithMixedProperties()],
            [new ClassWithMixedTypedProperties()],
            [new ClassWithMixedReferenceableTypedProperties()],
            [new ClassWithPrivateProperties()],
            [new ClassWithProtectedProperties()],
            [new ClassWithPublicProperties()],
            [new EmptyClass()],
            [new HydratedObject()],
            [new ClassWithSelfHint()],
            [new ClassWithParentHint()],
            [new ClassWithCollidingPrivateInheritedProperties()],
            [new ClassWithMethodWithVariadicFunction()],
            [new ClassWithMethodWithByRefVariadicFunction()],
            [new ScalarTypeHintedClass()],
            [new IterableTypeHintClass()],
            [new ObjectTypeHintClass()],
            [new ReturnTypeHintedClass()],
            [new VoidMethodTypeHintedClass()],
        ];
    }
}
