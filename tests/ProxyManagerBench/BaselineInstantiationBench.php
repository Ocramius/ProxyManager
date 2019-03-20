<?php

declare(strict_types=1);

namespace ProxyManagerBench;

use ProxyManagerTestAsset\ClassWithMixedProperties;
use ProxyManagerTestAsset\ClassWithPrivateProperties;
use ProxyManagerTestAsset\ClassWithProtectedProperties;
use ProxyManagerTestAsset\ClassWithPublicProperties;
use ProxyManagerTestAsset\EmptyClass;

/**
 * Benchmark that provides baseline results for simple object instantiation
 */
final class BaselineInstantiationBench
{
    public function benchInstantiationOfEmptyObject() : void
    {
        new EmptyClass();
    }

    public function benchInstantiationOfObjectWithPrivateProperties() : void
    {
        new ClassWithPrivateProperties();
    }

    public function benchInstantiationOfObjectWithProtectedProperties() : void
    {
        new ClassWithProtectedProperties();
    }

    public function benchInstantiationOfObjectWithPublicProperties() : void
    {
        new ClassWithPublicProperties();
    }

    public function benchInstantiationOfObjectWithMixedProperties() : void
    {
        new ClassWithMixedProperties();
    }
}
