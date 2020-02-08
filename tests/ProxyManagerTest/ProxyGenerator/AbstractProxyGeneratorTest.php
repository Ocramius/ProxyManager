<?php

declare(strict_types=1);

namespace ProxyManagerTest\ProxyGenerator;

use PHPUnit\Framework\TestCase;
use ProxyManager\Generator\ClassGenerator;
use ProxyManager\Generator\Util\UniqueIdentifierGenerator;
use ProxyManager\GeneratorStrategy\EvaluatingGeneratorStrategy;
use ProxyManager\ProxyGenerator\ProxyGeneratorInterface;
use ProxyManagerTestAsset\BaseClass;
use ProxyManagerTestAsset\BaseInterface;
use ProxyManagerTestAsset\ClassWithAbstractPublicMethod;
use ProxyManagerTestAsset\ClassWithByRefMagicMethods;
use ProxyManagerTestAsset\ClassWithMagicMethods;
use ProxyManagerTestAsset\ClassWithMixedProperties;
use ProxyManagerTestAsset\ClassWithMixedReferenceableTypedProperties;
use ProxyManagerTestAsset\ClassWithMixedTypedProperties;
use ProxyManagerTestAsset\IterableMethodTypeHintedInterface;
use ProxyManagerTestAsset\ObjectMethodTypeHintedInterface;
use ProxyManagerTestAsset\ReturnTypeHintedClass;
use ProxyManagerTestAsset\ReturnTypeHintedInterface;
use ProxyManagerTestAsset\VoidMethodTypeHintedClass;
use ProxyManagerTestAsset\VoidMethodTypeHintedInterface;
use ReflectionClass;

/**
 * Base test for proxy generators
 *
 * @group Coverage
 */
abstract class AbstractProxyGeneratorTest extends TestCase
{
    /**
     * @dataProvider getTestedImplementations
     *
     * Verifies that generated code is valid and implements expected interfaces
     * @psalm-param class-string $className
     */
    public function testGeneratesValidCode(string $className) : void
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
        } else {
            $parentClass = $generatedReflection->getParentClass();

            self::assertInstanceOf(ReflectionClass::class, $parentClass);
            self::assertSame($originalClass->getName(), $parentClass->getName());
        }

        self::assertSame($generatedClassName, $generatedReflection->getName());

        foreach ($this->getExpectedImplementedInterfaces() as $interface) {
            self::assertTrue($generatedReflection->implementsInterface($interface));
        }
    }

    /**
     * Retrieve a new generator instance
     */
    abstract protected function getProxyGenerator() : ProxyGeneratorInterface;

    /**
     * Retrieve interfaces that should be implemented by the generated code
     *
     * @return string[]
     *
     * @psalm-return list<class-string>
     */
    abstract protected function getExpectedImplementedInterfaces() : array;

    /** @return string[][] */
    public function getTestedImplementations() : array
    {
        return [
            [BaseClass::class],
            [ClassWithMagicMethods::class],
            [ClassWithByRefMagicMethods::class],
            [ClassWithMixedProperties::class],
            [ClassWithMixedTypedProperties::class],
            [ClassWithMixedReferenceableTypedProperties::class],
            [ClassWithAbstractPublicMethod::class],
            [BaseInterface::class],
            [ReturnTypeHintedClass::class],
            [VoidMethodTypeHintedClass::class],
            [ReturnTypeHintedInterface::class],
            [VoidMethodTypeHintedInterface::class],
            [IterableMethodTypeHintedInterface::class],
            [ObjectMethodTypeHintedInterface::class],
        ];
    }
}
