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

/**
 * Benchmark that provides results for simple object instantiation for lazy loading ghost proxies
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 *
 * @BeforeMethods({"setUp"})
 */
class LazyLoadingGhostInstantiationBench
{
    /**
     * @var string
     */
    private $emptyClassProxy;

    /**
     * @var string
     */
    private $privatePropertiesProxy;

    /**
     * @var string
     */
    private $protectedPropertiesProxy;

    /**
     * @var string
     */
    private $publicPropertiesProxy;

    /**
     * @var string
     */
    private $mixedPropertiesProxy;

    public function setUp()
    {
        $this->emptyClassProxy          = $this->generateProxy(EmptyClass::class);
        $this->privatePropertiesProxy   = $this->generateProxy(ClassWithPrivateProperties::class);
        $this->protectedPropertiesProxy = $this->generateProxy(ClassWithProtectedProperties::class);
        $this->publicPropertiesProxy    = $this->generateProxy(ClassWithPublicProperties::class);
        $this->mixedPropertiesProxy     = $this->generateProxy(ClassWithMixedProperties::class);
    }

    public function benchOriginalConstructorInstantiationOfEmptyObject() : void
    {
        new $this->emptyClassProxy;
    }

    public function benchInstantiationOfEmptyObject() : void
    {
        ($this->emptyClassProxy)::staticProxyConstructor(function () {
        });
    }

    public function benchOriginalConstructorInstantiationOfObjectWithPrivateProperties() : void
    {
        new $this->privatePropertiesProxy;
    }

    public function benchInstantiationOfObjectWithPrivateProperties() : void
    {
        ($this->privatePropertiesProxy)::staticProxyConstructor(function () {
        });
    }

    public function benchOriginalConstructorInstantiationOfObjectWithProtectedProperties() : void
    {
        new $this->protectedPropertiesProxy;
    }

    public function benchInstantiationOfObjectWithProtectedProperties() : void
    {
        ($this->protectedPropertiesProxy)::staticProxyConstructor(function () {
        });
    }

    public function benchOriginalConstructorInstantiationOfObjectWithPublicProperties() : void
    {
        new $this->publicPropertiesProxy;
    }

    public function benchInstantiationOfObjectWithPublicProperties() : void
    {
        ($this->publicPropertiesProxy)::staticProxyConstructor(function () {
        });
    }

    public function benchOriginalConstructorInstantiationOfObjectWithMixedProperties() : void
    {
        new $this->mixedPropertiesProxy;
    }

    public function benchInstantiationOfObjectWithMixedProperties() : void
    {
        ($this->mixedPropertiesProxy)::staticProxyConstructor(function () {
        });
    }

    private function generateProxy(string $originalClass) : string
    {
        $generatedClassName = __CLASS__ . '\\' . $originalClass;

        if (class_exists($generatedClassName)) {
            return $generatedClassName;
        }

        $generatedClass     = new ClassGenerator($generatedClassName);

        (new LazyLoadingGhostGenerator())->generate(new ReflectionClass($originalClass), $generatedClass, []);
        (new EvaluatingGeneratorStrategy())->generate($generatedClass);

        return $generatedClassName;
    }
}
