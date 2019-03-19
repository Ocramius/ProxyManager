<?php

declare(strict_types=1);

namespace ProxyManagerBench;

use PhpBench\Benchmark\Metadata\Annotations\BeforeMethods;
use ProxyManagerTestAsset\ClassWithMixedProperties;
use ProxyManagerTestAsset\ClassWithPrivateProperties;
use ProxyManagerTestAsset\ClassWithProtectedProperties;
use ProxyManagerTestAsset\ClassWithPublicProperties;
use ReflectionProperty;

/**
 * Benchmark that provides baseline results for simple object state interactions
 *
 * @BeforeMethods({"setUp"})
 */
final class BaselinePropertyAccessBench
{
    private ClassWithPrivateProperties $privateProperties;
    private ReflectionProperty $accessPrivateProperty;
    private ClassWithProtectedProperties $protectedProperties;
    private ReflectionProperty $accessProtectedProperty;
    private ClassWithPublicProperties $publicProperties;
    private ClassWithMixedProperties $mixedProperties;
    private ReflectionProperty $accessMixedPropertiesPrivate;
    private ReflectionProperty $accessMixedPropertiesProtected;

    public function setUp() : void
    {
        $this->privateProperties   = new ClassWithPrivateProperties();
        $this->protectedProperties = new ClassWithProtectedProperties();
        $this->publicProperties    = new ClassWithPublicProperties();
        $this->mixedProperties     = new ClassWithMixedProperties();

        $this->accessPrivateProperty = new ReflectionProperty(ClassWithPrivateProperties::class, 'property0');
        $this->accessPrivateProperty->setAccessible(true);

        $this->accessProtectedProperty = new ReflectionProperty(ClassWithProtectedProperties::class, 'property0');
        $this->accessProtectedProperty->setAccessible(true);

        $this->accessMixedPropertiesPrivate = new ReflectionProperty(
            ClassWithMixedProperties::class,
            'privateProperty0'
        );
        $this->accessMixedPropertiesPrivate->setAccessible(true);

        $this->accessMixedPropertiesProtected = new ReflectionProperty(
            ClassWithMixedProperties::class,
            'protectedProperty0'
        );
        $this->accessMixedPropertiesProtected->setAccessible(true);
    }

    public function benchPrivatePropertyRead() : void
    {
        $this->accessPrivateProperty->getValue($this->privateProperties);
    }

    public function benchPrivatePropertyWrite() : void
    {
        $this->accessPrivateProperty->setValue($this->privateProperties, 'foo');
    }

    public function benchProtectedPropertyRead() : void
    {
        $this->accessProtectedProperty->getValue($this->protectedProperties);
    }

    public function benchProtectedPropertyWrite() : void
    {
        $this->accessProtectedProperty->setValue($this->protectedProperties, 'foo');
    }

    public function benchPublicPropertyRead() : void
    {
        $this->publicProperties->property0;
    }

    public function benchPublicPropertyWrite() : void
    {
        $this->publicProperties->property0 = 'foo';
    }

    public function benchMixedPropertiesPrivatePropertyRead() : void
    {
        $this->accessMixedPropertiesPrivate->getValue($this->mixedProperties);
    }

    public function benchMixedPropertiesPrivatePropertyWrite() : void
    {
        $this->accessMixedPropertiesPrivate->setValue($this->mixedProperties, 'foo');
    }

    public function benchMixedPropertiesProtectedPropertyRead() : void
    {
        $this->accessMixedPropertiesProtected->getValue($this->mixedProperties);
    }

    public function benchMixedPropertiesProtectedPropertyWrite() : void
    {
        $this->accessMixedPropertiesProtected->setValue($this->mixedProperties, 'foo');
    }

    public function benchMixedPropertiesPublicPropertyRead() : void
    {
        $this->mixedProperties->publicProperty0;
    }

    public function benchMixedPropertiesPublicPropertyWrite() : void
    {
        $this->mixedProperties->publicProperty0 = 'foo';
    }
}
