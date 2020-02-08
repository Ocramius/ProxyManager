<?php

declare(strict_types=1);

namespace ProxyManagerBench;

use Closure;
use PhpBench\Benchmark\Metadata\Annotations\BeforeMethods;
use ProxyManager\Generator\ClassGenerator;
use ProxyManager\GeneratorStrategy\EvaluatingGeneratorStrategy;
use ProxyManager\Proxy\ValueHolderInterface;
use ProxyManager\Proxy\VirtualProxyInterface;
use ProxyManager\ProxyGenerator\LazyLoadingValueHolderGenerator;
use ProxyManagerTestAsset\ClassWithMixedProperties;
use ProxyManagerTestAsset\ClassWithPublicProperties;
use ProxyManagerTestAsset\EmptyClass;
use ReflectionClass;
use function assert;
use function class_exists;
use function is_a;

/**
 * Benchmark that provides results for state access/initialization time for lazy loading value holder proxies
 *
 * @BeforeMethods({"setUp"})
 */
final class LazyLoadingValueHolderPropertyAccessBench
{
    /** @var EmptyClass&VirtualProxyInterface */
    private EmptyClass $emptyClassProxy;

    /** @var EmptyClass&VirtualProxyInterface */
    private EmptyClass $initializedEmptyClassProxy;

    /** @var ClassWithPublicProperties&VirtualProxyInterface */
    private ClassWithPublicProperties $publicPropertiesProxy;

    /** @var ClassWithPublicProperties&VirtualProxyInterface */
    private ClassWithPublicProperties $initializedPublicPropertiesProxy;

    /** @var ClassWithMixedProperties&VirtualProxyInterface */
    private ClassWithMixedProperties $mixedPropertiesProxy;

    /** @var ClassWithMixedProperties&VirtualProxyInterface */
    private ClassWithMixedProperties $initializedMixedPropertiesProxy;

    public function setUp() : void
    {
        $emptyClassProxy                  = $this->buildProxy(EmptyClass::class);
        $publicPropertiesProxy            = $this->buildProxy(ClassWithPublicProperties::class);
        $mixedPropertiesProxy             = $this->buildProxy(ClassWithMixedProperties::class);
        $initializedEmptyClassProxy       = $this->buildProxy(EmptyClass::class);
        $initializedPublicPropertiesProxy = $this->buildProxy(ClassWithPublicProperties::class);
        $initializedMixedPropertiesProxy  = $this->buildProxy(ClassWithMixedProperties::class);

        assert($emptyClassProxy instanceof VirtualProxyInterface);
        assert($publicPropertiesProxy instanceof VirtualProxyInterface);
        assert($mixedPropertiesProxy instanceof VirtualProxyInterface);
        assert($initializedEmptyClassProxy instanceof VirtualProxyInterface);
        assert($initializedPublicPropertiesProxy instanceof VirtualProxyInterface);
        assert($initializedMixedPropertiesProxy instanceof VirtualProxyInterface);

        assert($emptyClassProxy instanceof EmptyClass);
        assert($publicPropertiesProxy instanceof ClassWithPublicProperties);
        assert($mixedPropertiesProxy instanceof ClassWithMixedProperties);
        assert($initializedEmptyClassProxy instanceof EmptyClass);
        assert($initializedPublicPropertiesProxy instanceof ClassWithPublicProperties);
        assert($initializedMixedPropertiesProxy instanceof ClassWithMixedProperties);

        $this->emptyClassProxy       = $emptyClassProxy;
        $this->publicPropertiesProxy = $publicPropertiesProxy;
        $this->mixedPropertiesProxy  = $mixedPropertiesProxy;

        $this->initializedEmptyClassProxy       = $initializedEmptyClassProxy;
        $this->initializedPublicPropertiesProxy = $initializedPublicPropertiesProxy;
        $this->initializedMixedPropertiesProxy  = $initializedMixedPropertiesProxy;

        $this->initializedEmptyClassProxy->initializeProxy();
        $this->initializedPublicPropertiesProxy->initializeProxy();
        $this->initializedMixedPropertiesProxy->initializeProxy();
    }

    public function benchEmptyClassInitialization() : void
    {
        $this->emptyClassProxy->initializeProxy();
    }

    public function benchInitializedEmptyClassInitialization() : void
    {
        $this->initializedEmptyClassProxy->initializeProxy();
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
     * @psalm-return OriginalClass&ValueHolderInterface<OriginalClass>&VirtualProxyInterface
     * @psalm-suppress MixedInferredReturnType
     */
    private function buildProxy(string $originalClass) : VirtualProxyInterface
    {
        /**
         * @psalm-suppress MixedReturnStatement
         * @psalm-suppress MixedMethodCall
         */
        return $this->generateProxyClass($originalClass)::staticProxyConstructor(
            static function (
                ?object & $valueHolder,
                VirtualProxyInterface $proxy,
                string $method,
                array $params,
                ?Closure & $initializer
            ) use ($originalClass) : bool {
                $initializer = null;

                $valueHolder = new $originalClass();

                return true;
            }
        );
    }

    /**
     * @psalm-template OriginalClass
     * @psalm-param class-string<OriginalClass> $originalClassName
     * @psalm-return class-string<OriginalClass>
     * @psalm-suppress MoreSpecificReturnType
     */
    private function generateProxyClass(string $originalClassName) : string
    {
        $generatedClassName = self::class . '\\' . $originalClassName;

        if (class_exists($generatedClassName)) {
            assert(is_a($generatedClassName, $originalClassName, true));

            return $generatedClassName;
        }

        $generatedClass = new ClassGenerator($generatedClassName);

        (new LazyLoadingValueHolderGenerator())->generate(new ReflectionClass($originalClassName), $generatedClass);
        (new EvaluatingGeneratorStrategy())->generate($generatedClass);

        /** @psalm-suppress LessSpecificReturnStatement */
        return $generatedClassName;
    }
}
