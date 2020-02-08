<?php

declare(strict_types=1);

namespace ProxyManagerBench;

use PhpBench\Benchmark\Metadata\Annotations\BeforeMethods;
use ProxyManager\Generator\ClassGenerator;
use ProxyManager\GeneratorStrategy\EvaluatingGeneratorStrategy;
use ProxyManager\ProxyGenerator\LazyLoadingGhostGenerator;
use ProxyManagerTestAsset\ClassWithMixedProperties;
use ProxyManagerTestAsset\ClassWithPrivateProperties;
use ProxyManagerTestAsset\ClassWithProtectedProperties;
use ProxyManagerTestAsset\ClassWithPublicProperties;
use ProxyManagerTestAsset\EmptyClass;
use ReflectionClass;
use function assert;
use function class_exists;
use function is_a;

/**
 * Benchmark that provides results for simple object instantiation for lazy loading ghost proxies
 *
 * @BeforeMethods({"setUp"})
 */
final class LazyLoadingGhostInstantiationBench
{
    /** @psalm-var class-string<EmptyClass> */
    private string $emptyClassProxy;
    /** @psalm-var class-string<ClassWithPrivateProperties> */
    private string $privatePropertiesProxy;
    /** @psalm-var class-string<ClassWithProtectedProperties> */
    private string $protectedPropertiesProxy;
    /** @psalm-var class-string<ClassWithPublicProperties> */
    private string $publicPropertiesProxy;
    /** @psalm-var class-string<ClassWithMixedProperties> */
    private string $mixedPropertiesProxy;

    public function setUp() : void
    {
        $this->emptyClassProxy          = $this->generateProxy(EmptyClass::class);
        $this->privatePropertiesProxy   = $this->generateProxy(ClassWithPrivateProperties::class);
        $this->protectedPropertiesProxy = $this->generateProxy(ClassWithProtectedProperties::class);
        $this->publicPropertiesProxy    = $this->generateProxy(ClassWithPublicProperties::class);
        $this->mixedPropertiesProxy     = $this->generateProxy(ClassWithMixedProperties::class);
    }

    public function benchOriginalConstructorInstantiationOfEmptyObject() : void
    {
        new $this->emptyClassProxy();
    }

    public function benchInstantiationOfEmptyObject() : void
    {
        /** @psalm-suppress UndefinedMethod */
        $this->emptyClassProxy::staticProxyConstructor(static function () : void {
        });
    }

    public function benchOriginalConstructorInstantiationOfObjectWithPrivateProperties() : void
    {
        new $this->privatePropertiesProxy();
    }

    public function benchInstantiationOfObjectWithPrivateProperties() : void
    {
        /** @psalm-suppress UndefinedMethod */
        $this->privatePropertiesProxy::staticProxyConstructor(static function () : void {
        });
    }

    public function benchOriginalConstructorInstantiationOfObjectWithProtectedProperties() : void
    {
        new $this->protectedPropertiesProxy();
    }

    public function benchInstantiationOfObjectWithProtectedProperties() : void
    {
        /** @psalm-suppress UndefinedMethod */
        $this->protectedPropertiesProxy::staticProxyConstructor(static function () : void {
        });
    }

    public function benchOriginalConstructorInstantiationOfObjectWithPublicProperties() : void
    {
        new $this->publicPropertiesProxy();
    }

    public function benchInstantiationOfObjectWithPublicProperties() : void
    {
        /** @psalm-suppress UndefinedMethod */
        $this->publicPropertiesProxy::staticProxyConstructor(static function () : void {
        });
    }

    public function benchOriginalConstructorInstantiationOfObjectWithMixedProperties() : void
    {
        new $this->mixedPropertiesProxy();
    }

    public function benchInstantiationOfObjectWithMixedProperties() : void
    {
        /** @psalm-suppress UndefinedMethod */
        $this->mixedPropertiesProxy::staticProxyConstructor(static function () : void {
        });
    }

    /**
     * @psalm-template OriginalClass
     * @psalm-param class-string<OriginalClass> $originalClass
     * @psalm-return class-string<OriginalClass>
     * @psalm-suppress MoreSpecificReturnType
     */
    private function generateProxy(string $originalClass) : string
    {
        $generatedClassName = self::class . '\\' . $originalClass;

        if (class_exists($generatedClassName)) {
            assert(is_a($generatedClassName, $originalClass, true));

            return $generatedClassName;
        }

        $generatedClass = new ClassGenerator($generatedClassName);

        (new LazyLoadingGhostGenerator())->generate(new ReflectionClass($originalClass), $generatedClass, []);
        (new EvaluatingGeneratorStrategy())->generate($generatedClass);

        /** @psalm-suppress LessSpecificReturnStatement */
        return $generatedClassName;
    }
}
