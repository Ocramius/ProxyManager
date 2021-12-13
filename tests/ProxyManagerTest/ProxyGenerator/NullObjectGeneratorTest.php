<?php

declare(strict_types=1);

namespace ProxyManagerTest\ProxyGenerator;

use Laminas\Code\Generator\ClassGenerator;
use ProxyManager\Generator\Util\UniqueIdentifierGenerator;
use ProxyManager\GeneratorStrategy\EvaluatingGeneratorStrategy;
use ProxyManager\Proxy\NullObjectInterface;
use ProxyManager\ProxyGenerator\NullObjectGenerator;
use ProxyManager\ProxyGenerator\ProxyGeneratorInterface;
use ProxyManager\ProxyGenerator\Util\Properties;
use ProxyManagerTestAsset\BaseClass;
use ProxyManagerTestAsset\BaseInterface;
use ProxyManagerTestAsset\ClassWithByRefMagicMethods;
use ProxyManagerTestAsset\ClassWithMagicMethods;
use ProxyManagerTestAsset\ClassWithMixedProperties;
use ProxyManagerTestAsset\ClassWithMixedReferenceableTypedProperties;
use ProxyManagerTestAsset\ClassWithMixedTypedProperties;
use ProxyManagerTestAsset\ClassWithPhp80TypedMethods;
use ReflectionClass;
use ReflectionMethod;

use const PHP_VERSION_ID;

/**
 * Tests for {@see \ProxyManager\ProxyGenerator\NullObjectGenerator}
 *
 * @covers \ProxyManager\ProxyGenerator\NullObjectGenerator
 * @group Coverage
 */
final class NullObjectGeneratorTest extends AbstractProxyGeneratorTest
{
    /**
     * @psalm-param class-string $className
     *
     * @dataProvider getTestedImplementations
     *
     * Verifies that generated code is valid and implements expected interfaces
     * @psalm-suppress MoreSpecificImplementedParamType
     */
    public function testGeneratesValidCode(string $className): void
    {
        $generator          = $this->getProxyGenerator();
        $generatedClassName = UniqueIdentifierGenerator::getIdentifier('AbstractProxyGeneratorTest');
        $generatedClass     = new ClassGenerator($generatedClassName);
        $originalClass      = new ReflectionClass($className);
        $generatorStrategy  = new EvaluatingGeneratorStrategy();

        $generator->generate($originalClass, $generatedClass);
        $generatorStrategy->generate($generatedClass);

        $generatedReflection = new ReflectionClass($generatedClassName);

        if ($originalClass->isInterface()) {
            self::assertTrue($generatedReflection->implementsInterface($className));
        }

        self::assertSame($generatedClassName, $generatedReflection->getName());

        foreach ($this->getExpectedImplementedInterfaces() as $interface) {
            self::assertTrue($generatedReflection->implementsInterface($interface));
        }

        /**
         * @psalm-suppress InvalidStringClass
         * @psalm-suppress MixedAssignment
         * @psalm-suppress MixedMethodCall
         */
        $proxy = $generatedClassName::staticProxyConstructor();

        self::assertInstanceOf($className, $proxy);

        foreach (
            Properties::fromReflectionClass($generatedReflection)
                ->onlyNullableProperties()
                ->getPublicProperties() as $property
        ) {
            /** @psalm-suppress MixedPropertyFetch */
            self::assertNull($proxy->{$property->getName()});
        }

        foreach ($generatedReflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            if ($method->getNumberOfParameters() || $method->isStatic()) {
                continue;
            }

            $callback = [$proxy, $method->getName()];

            self::assertIsCallable($callback);
            self::assertNull($callback());
        }
    }

    protected function getProxyGenerator(): ProxyGeneratorInterface
    {
        return new NullObjectGenerator();
    }

    /**
     * {@inheritDoc}
     */
    protected function getExpectedImplementedInterfaces(): array
    {
        return [
            NullObjectInterface::class,
        ];
    }

    /**
     * @psalm-return array<int, array<int, class-string>>
     */
    public function getTestedImplementations(): array
    {
        $implementations = [
            [BaseClass::class],
            [ClassWithMagicMethods::class],
            [ClassWithByRefMagicMethods::class],
            [ClassWithMixedProperties::class],
            [ClassWithMixedTypedProperties::class],
            [ClassWithMixedReferenceableTypedProperties::class],
            [BaseInterface::class],
        ];

        if (PHP_VERSION_ID >= 80000) {
            $implementations[] = [ClassWithPhp80TypedMethods::class];
        }

        return $implementations;
    }
}
