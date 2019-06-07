<?php

declare(strict_types=1);

namespace ProxyManagerTest\ProxyGenerator\Util;

use PHPUnit\Framework\TestCase;
use ProxyManager\ProxyGenerator\Util\ProxiedMethodsFilter;
use ProxyManagerTestAsset\BaseClass;
use ProxyManagerTestAsset\ClassWithAbstractMagicMethods;
use ProxyManagerTestAsset\ClassWithAbstractProtectedMethod;
use ProxyManagerTestAsset\ClassWithAbstractPublicMethod;
use ProxyManagerTestAsset\ClassWithCounterConstructor;
use ProxyManagerTestAsset\ClassWithFinalMagicMethods;
use ProxyManagerTestAsset\ClassWithMagicMethods;
use ProxyManagerTestAsset\ClassWithMethodWithByRefVariadicFunction;
use ProxyManagerTestAsset\ClassWithMethodWithVariadicFunction;
use ProxyManagerTestAsset\EmptyClass;
use ProxyManagerTestAsset\HydratedObject;
use ProxyManagerTestAsset\LazyLoadingMock;
use ReflectionClass;
use ReflectionMethod;
use function array_map;
use function sort;

/**
 * Tests for {@see \ProxyManager\ProxyGenerator\Util\ProxiedMethodsFilter}
 *
 * @covers \ProxyManager\ProxyGenerator\Util\ProxiedMethodsFilter
 * @group Coverage
 */
final class ProxiedMethodsFilterTest extends TestCase
{
    /**
     * @param array<int, string>|null $excludes
     * @param string[]                $expectedMethods
     *
     * @dataProvider expectedMethods
     */
    public function testFiltering(ReflectionClass $reflectionClass, ?array $excludes, array $expectedMethods) : void
    {
        $filtered = ProxiedMethodsFilter::getProxiedMethods($reflectionClass, $excludes);

        $keys = array_map(
            static function (ReflectionMethod $method) : string {
                return $method->getName();
            },
            $filtered
        );

        sort($keys);
        sort($expectedMethods);

        self::assertSame($keys, $expectedMethods);
    }

    /**
     * @param array<int, string>|null $excludes
     * @param string[]                $expectedMethods
     *
     * @dataProvider expectedAbstractPublicMethods
     */
    public function testFilteringOfAbstractPublic(
        ReflectionClass $reflectionClass,
        ?array $excludes,
        array $expectedMethods
    ) : void {
        $filtered = ProxiedMethodsFilter::getAbstractProxiedMethods($reflectionClass, $excludes);

        $keys = array_map(
            static function (ReflectionMethod $method) : string {
                return $method->getName();
            },
            $filtered
        );

        sort($keys);
        sort($expectedMethods);

        self::assertSame($keys, $expectedMethods);
    }

    /**
     * Data provider
     *
     * @return ReflectionClass[][]|null[][]|string[][][]
     */
    public function expectedMethods() : array
    {
        return [
            [
                new ReflectionClass(BaseClass::class),
                null,
                [
                    'privatePropertyGetter',
                    'protectedPropertyGetter',
                    'publicArrayHintedMethod',
                    'publicByReferenceMethod',
                    'publicByReferenceParameterMethod',
                    'publicMethod',
                    'publicPropertyGetter',
                    'publicTypeHintedMethod',
                ],
            ],
            [
                new ReflectionClass(EmptyClass::class),
                null,
                [],
            ],
            [
                new ReflectionClass(LazyLoadingMock::class),
                null,
                [
                    'getProxyInitializer',
                    'getWrappedValueHolderValue',
                    'initializeProxy',
                    'isProxyInitialized',
                    'setProxyInitializer',
                ],
            ],
            [
                new ReflectionClass(LazyLoadingMock::class),
                [],
                [
                    'getProxyInitializer',
                    'getWrappedValueHolderValue',
                    'initializeProxy',
                    'isProxyInitialized',
                    'setProxyInitializer',
                ],
            ],
            [
                new ReflectionClass(HydratedObject::class),
                ['doFoo'],
                ['__get'],
            ],
            [
                new ReflectionClass(HydratedObject::class),
                ['Dofoo'],
                ['__get'],
            ],
            [
                new ReflectionClass(HydratedObject::class),
                [],
                ['doFoo', '__get'],
            ],
            [
                new ReflectionClass(ClassWithAbstractProtectedMethod::class),
                null,
                [],
            ],
            [
                new ReflectionClass(ClassWithAbstractPublicMethod::class),
                null,
                ['publicAbstractMethod'],
            ],
            [
                new ReflectionClass(ClassWithAbstractPublicMethod::class),
                ['publicAbstractMethod'],
                [],
            ],
            [
                new ReflectionClass(ClassWithAbstractMagicMethods::class),
                null,
                [],
            ],
            [
                new ReflectionClass(ClassWithAbstractMagicMethods::class),
                [],
                [
                    '__clone',
                    '__get',
                    '__isset',
                    '__set',
                    '__sleep',
                    '__unset',
                    '__wakeup',
                ],
            ],
            [
                new ReflectionClass(ClassWithMethodWithVariadicFunction::class),
                null,
                ['foo', 'buz'],
            ],
            [
                new ReflectionClass(ClassWithMethodWithByRefVariadicFunction::class),
                null,
                ['tuz'],
            ],
            'final magic methods' => [
                new ReflectionClass(ClassWithFinalMagicMethods::class),
                null,
                [],
            ],
            'non-final constructor is to be skipped' => [
                new ReflectionClass(ClassWithCounterConstructor::class),
                null,
                ['getAmount'],
            ],
        ];
    }

    /**
     * Data provider
     *
     * @return ReflectionClass[][]|null[][]|string[][][]
     */
    public function expectedAbstractPublicMethods() : array
    {
        return [
            [
                new ReflectionClass(BaseClass::class),
                null,
                [],
            ],
            [
                new ReflectionClass(EmptyClass::class),
                null,
                [],
            ],
            [
                new ReflectionClass(ClassWithAbstractProtectedMethod::class),
                null,
                [],
            ],
            [
                new ReflectionClass(ClassWithAbstractPublicMethod::class),
                null,
                ['publicAbstractMethod'],
            ],
            [
                new ReflectionClass(ClassWithAbstractPublicMethod::class),
                ['publicAbstractMethod'],
                [],
            ],
            [
                new ReflectionClass(ClassWithMagicMethods::class),
                [],
                [],
            ],
            [
                new ReflectionClass(ClassWithAbstractMagicMethods::class),
                null,
                [],
            ],
            [
                new ReflectionClass(ClassWithAbstractMagicMethods::class),
                [],
                [
                    '__clone',
                    '__get',
                    '__isset',
                    '__set',
                    '__sleep',
                    '__unset',
                    '__wakeup',
                ],
            ],
        ];
    }
}
