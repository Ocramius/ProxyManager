<?php

declare(strict_types=1);

namespace ProxyManagerBench;

use Closure;
use PhpBench\Benchmark\Metadata\Annotations\BeforeMethods;
use ProxyManager\Factory\LazyLoadingGhostFactory;
use ProxyManager\Proxy\GhostObjectInterface;
use ProxyManager\Proxy\LazyLoadingInterface;
use ProxyManagerTestAsset\ClassWithMixedProperties;
use ProxyManagerTestAsset\ClassWithPrivateProperties;
use ProxyManagerTestAsset\ClassWithProtectedProperties;
use ProxyManagerTestAsset\ClassWithPublicProperties;
use ProxyManagerTestAsset\EmptyClass;
use ReflectionProperty;
use function assert;

/**
 * Benchmark that provides results for simple initialization/state access for lazy loading ghost proxies
 *
 * @BeforeMethods({"setUp"})
 */
final class LazyLoadingGhostPropertyAccessBench
{
    /** @var EmptyClass&LazyLoadingInterface */
    private EmptyClass $emptyClassProxy;

    /** @var EmptyClass&LazyLoadingInterface */
    private EmptyClass $initializedEmptyClassProxy;

    /** @var ClassWithPrivateProperties&LazyLoadingInterface */
    private ClassWithPrivateProperties $privatePropertiesProxy;

    /** @var ClassWithPrivateProperties&LazyLoadingInterface */
    private ClassWithPrivateProperties $initializedPrivatePropertiesProxy;
    private ReflectionProperty $accessPrivateProperty;

    /** @var ClassWithProtectedProperties&LazyLoadingInterface */
    private ClassWithProtectedProperties $protectedPropertiesProxy;

    /** @var ClassWithProtectedProperties&LazyLoadingInterface */
    private ClassWithProtectedProperties $initializedProtectedPropertiesProxy;
    private ReflectionProperty $accessProtectedProperty;

    /** @var ClassWithPublicProperties&LazyLoadingInterface */
    private ClassWithPublicProperties $publicPropertiesProxy;

    /** @var ClassWithPublicProperties&LazyLoadingInterface */
    private ClassWithPublicProperties $initializedPublicPropertiesProxy;

    /** @var ClassWithMixedProperties&LazyLoadingInterface */
    private ClassWithMixedProperties $mixedPropertiesProxy;

    /** @var ClassWithMixedProperties&LazyLoadingInterface */
    private ClassWithMixedProperties $initializedMixedPropertiesProxy;

    public function setUp() : void
    {
        $emptyClassProxy                     = $this->buildProxy(EmptyClass::class);
        $privatePropertiesProxy              = $this->buildProxy(ClassWithPrivateProperties::class);
        $protectedPropertiesProxy            = $this->buildProxy(ClassWithProtectedProperties::class);
        $publicPropertiesProxy               = $this->buildProxy(ClassWithPublicProperties::class);
        $mixedPropertiesProxy                = $this->buildProxy(ClassWithMixedProperties::class);
        $initializedEmptyClassProxy          = $this->buildProxy(EmptyClass::class);
        $initializedPrivatePropertiesProxy   = $this->buildProxy(ClassWithPrivateProperties::class);
        $initializedProtectedPropertiesProxy = $this->buildProxy(ClassWithProtectedProperties::class);
        $initializedPublicPropertiesProxy    = $this->buildProxy(ClassWithPublicProperties::class);
        $initializedMixedPropertiesProxy     = $this->buildProxy(ClassWithMixedProperties::class);

        assert($emptyClassProxy instanceof LazyLoadingInterface);
        assert($privatePropertiesProxy instanceof LazyLoadingInterface);
        assert($protectedPropertiesProxy instanceof LazyLoadingInterface);
        assert($publicPropertiesProxy instanceof LazyLoadingInterface);
        assert($mixedPropertiesProxy instanceof LazyLoadingInterface);
        assert($initializedEmptyClassProxy instanceof LazyLoadingInterface);
        assert($initializedPrivatePropertiesProxy instanceof LazyLoadingInterface);
        assert($initializedProtectedPropertiesProxy instanceof LazyLoadingInterface);
        assert($initializedPublicPropertiesProxy instanceof LazyLoadingInterface);
        assert($initializedMixedPropertiesProxy instanceof LazyLoadingInterface);

        assert($emptyClassProxy instanceof EmptyClass);
        assert($privatePropertiesProxy instanceof ClassWithPrivateProperties);
        assert($protectedPropertiesProxy instanceof ClassWithProtectedProperties);
        assert($publicPropertiesProxy instanceof ClassWithPublicProperties);
        assert($mixedPropertiesProxy instanceof ClassWithMixedProperties);
        assert($initializedEmptyClassProxy instanceof EmptyClass);
        assert($initializedPrivatePropertiesProxy instanceof ClassWithPrivateProperties);
        assert($initializedProtectedPropertiesProxy instanceof ClassWithProtectedProperties);
        assert($initializedPublicPropertiesProxy instanceof ClassWithPublicProperties);
        assert($initializedMixedPropertiesProxy instanceof ClassWithMixedProperties);

        $this->emptyClassProxy          = $emptyClassProxy;
        $this->privatePropertiesProxy   = $privatePropertiesProxy;
        $this->protectedPropertiesProxy = $protectedPropertiesProxy;
        $this->publicPropertiesProxy    = $publicPropertiesProxy;
        $this->mixedPropertiesProxy     = $mixedPropertiesProxy;

        $this->initializedEmptyClassProxy          = $initializedEmptyClassProxy;
        $this->initializedPrivatePropertiesProxy   = $initializedPrivatePropertiesProxy;
        $this->initializedProtectedPropertiesProxy = $initializedProtectedPropertiesProxy;
        $this->initializedPublicPropertiesProxy    = $initializedPublicPropertiesProxy;
        $this->initializedMixedPropertiesProxy     = $initializedMixedPropertiesProxy;

        $this->initializedEmptyClassProxy->initializeProxy();
        $this->initializedPrivatePropertiesProxy->initializeProxy();
        $this->initializedProtectedPropertiesProxy->initializeProxy();
        $this->initializedPublicPropertiesProxy->initializeProxy();
        $this->initializedMixedPropertiesProxy->initializeProxy();

        $this->accessPrivateProperty = new ReflectionProperty(ClassWithPrivateProperties::class, 'property0');
        $this->accessPrivateProperty->setAccessible(true);

        $this->accessProtectedProperty = new ReflectionProperty(ClassWithProtectedProperties::class, 'property0');
        $this->accessProtectedProperty->setAccessible(true);
    }

    public function benchEmptyClassInitialization() : void
    {
        $this->emptyClassProxy->initializeProxy();
    }

    public function benchInitializedEmptyClassInitialization() : void
    {
        $this->initializedEmptyClassProxy->initializeProxy();
    }

    public function benchObjectWithPrivatePropertiesInitialization() : void
    {
        $this->privatePropertiesProxy->initializeProxy();
    }

    public function benchInitializedObjectWithPrivatePropertiesInitialization() : void
    {
        $this->initializedPrivatePropertiesProxy->initializeProxy();
    }

    public function benchObjectWithPrivatePropertiesPropertyRead() : void
    {
        $this->accessPrivateProperty->getValue($this->privatePropertiesProxy);
    }

    public function benchInitializedObjectWithPrivatePropertiesPropertyRead() : void
    {
        $this->accessPrivateProperty->getValue($this->initializedPrivatePropertiesProxy);
    }

    public function benchObjectWithPrivatePropertiesPropertyWrite() : void
    {
        $this->accessPrivateProperty->setValue($this->privatePropertiesProxy, 'foo');
    }

    public function benchInitializedObjectWithPrivatePropertiesPropertyWrite() : void
    {
        $this->accessPrivateProperty->setValue($this->initializedPrivatePropertiesProxy, 'foo');
    }

    public function benchObjectWithProtectedPropertiesInitialization() : void
    {
        $this->protectedPropertiesProxy->initializeProxy();
    }

    public function benchInitializedObjectWithProtectedPropertiesInitialization() : void
    {
        $this->initializedProtectedPropertiesProxy->initializeProxy();
    }

    public function benchObjectWithProtectedPropertiesPropertyRead() : void
    {
        $this->accessProtectedProperty->getValue($this->protectedPropertiesProxy);
    }

    public function benchInitializedObjectWithProtectedPropertiesPropertyRead() : void
    {
        $this->accessProtectedProperty->getValue($this->initializedProtectedPropertiesProxy);
    }

    public function benchObjectWithProtectedPropertiesPropertyWrite() : void
    {
        $this->accessProtectedProperty->setValue($this->protectedPropertiesProxy, 'foo');
    }

    public function benchInitializedObjectWithProtectedPropertiesPropertyWrite() : void
    {
        $this->accessProtectedProperty->setValue($this->initializedProtectedPropertiesProxy, 'foo');
    }

    public function benchObjectWithPublicPropertiesInitialization() : void
    {
        $this->publicPropertiesProxy->initializeProxy();
    }

    public function benchInitializedObjectWithPublicPropertiesInitialization() : void
    {
        $this->initializedPublicPropertiesProxy->initializeProxy();
    }

    public function benchObjectWithPublicPropertiesPropertyRead() : void
    {
        $this->publicPropertiesProxy->property0;
    }

    public function benchInitializedObjectWithPublicPropertiesPropertyRead() : void
    {
        $this->initializedPublicPropertiesProxy->property0;
    }

    public function benchObjectWithPublicPropertiesPropertyWrite() : void
    {
        $this->publicPropertiesProxy->property0 = 'foo';
    }

    public function benchInitializedObjectWithPublicPropertiesPropertyWrite() : void
    {
        $this->initializedPublicPropertiesProxy->property0 = 'foo';
    }

    public function benchObjectWithPublicPropertiesPropertyIsset() : void
    {
        /* @noinspection PhpExpressionResultUnusedInspection */
        /* @noinspection UnSafeIsSetOverArrayInspection */
        isset($this->publicPropertiesProxy->property0);
    }

    public function benchInitializedObjectWithPublicPropertiesPropertyIsset() : void
    {
        /* @noinspection PhpExpressionResultUnusedInspection */
        /* @noinspection UnSafeIsSetOverArrayInspection */
        isset($this->initializedPublicPropertiesProxy->property0);
    }

    public function benchObjectWithPublicPropertiesPropertyUnset() : void
    {
        unset($this->publicPropertiesProxy->property0);
    }

    public function benchInitializedObjectWithPublicPropertiesPropertyUnset() : void
    {
        unset($this->initializedPublicPropertiesProxy->property0);
    }

    public function benchObjectWithMixedPropertiesInitialization() : void
    {
        $this->mixedPropertiesProxy->initializeProxy();
    }

    public function benchInitializedObjectWithMixedPropertiesInitialization() : void
    {
        $this->initializedMixedPropertiesProxy->initializeProxy();
    }

    public function benchObjectWithMixedPropertiesPropertyRead() : void
    {
        $this->mixedPropertiesProxy->publicProperty0;
    }

    public function benchInitializedObjectWithMixedPropertiesPropertyRead() : void
    {
        $this->initializedMixedPropertiesProxy->publicProperty0;
    }

    public function benchObjectWithMixedPropertiesPropertyWrite() : void
    {
        $this->mixedPropertiesProxy->publicProperty0 = 'foo';
    }

    public function benchInitializedObjectWithMixedPropertiesPropertyWrite() : void
    {
        $this->initializedMixedPropertiesProxy->publicProperty0 = 'foo';
    }

    public function benchObjectWithMixedPropertiesPropertyIsset() : void
    {
        /* @noinspection PhpExpressionResultUnusedInspection */
        /* @noinspection UnSafeIsSetOverArrayInspection */
        isset($this->mixedPropertiesProxy->publicProperty0);
    }

    public function benchInitializedObjectWithMixedPropertiesPropertyIsset() : void
    {
        /* @noinspection PhpExpressionResultUnusedInspection */
        /* @noinspection UnSafeIsSetOverArrayInspection */
        isset($this->initializedMixedPropertiesProxy->publicProperty0);
    }

    public function benchObjectWithMixedPropertiesPropertyUnset() : void
    {
        unset($this->mixedPropertiesProxy->publicProperty0);
    }

    public function benchInitializedObjectWithMixedPropertiesPropertyUnset() : void
    {
        unset($this->initializedMixedPropertiesProxy->publicProperty0);
    }

    /**
     * @psalm-template OriginalClass
     * @psalm-param class-string<OriginalClass> $originalClass
     * @psalm-return OriginalClass&GhostObjectInterface<OriginalClass>
     * @psalm-suppress MixedInferredReturnType
     */
    private function buildProxy(string $originalClass) : GhostObjectInterface
    {
        return (new LazyLoadingGhostFactory())
            ->createProxy(
                $originalClass,
                static function (object $proxy, string $method, array $params, ?Closure & $initializer) : bool {
                    $initializer = null;

                    return true;
                }
            );
    }
}
