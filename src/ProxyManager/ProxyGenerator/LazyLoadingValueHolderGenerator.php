<?php

declare(strict_types=1);

namespace ProxyManager\ProxyGenerator;

use InvalidArgumentException;
use Laminas\Code\Generator\ClassGenerator;
use Laminas\Code\Generator\MethodGenerator;
use Laminas\Code\Reflection\MethodReflection;
use ProxyManager\Exception\InvalidProxiedClassException;
use ProxyManager\Generator\Util\ClassGeneratorUtils;
use ProxyManager\Proxy\VirtualProxyInterface;
use ProxyManager\ProxyGenerator\AccessInterceptor\MethodGenerator\MagicWakeup;
use ProxyManager\ProxyGenerator\Assertion\CanProxyAssertion;
use ProxyManager\ProxyGenerator\LazyLoading\MethodGenerator\StaticProxyConstructor;
use ProxyManager\ProxyGenerator\LazyLoadingValueHolder\MethodGenerator\GetProxyInitializer;
use ProxyManager\ProxyGenerator\LazyLoadingValueHolder\MethodGenerator\InitializeProxy;
use ProxyManager\ProxyGenerator\LazyLoadingValueHolder\MethodGenerator\IsProxyInitialized;
use ProxyManager\ProxyGenerator\LazyLoadingValueHolder\MethodGenerator\LazyLoadingMethodInterceptor;
use ProxyManager\ProxyGenerator\LazyLoadingValueHolder\MethodGenerator\MagicClone;
use ProxyManager\ProxyGenerator\LazyLoadingValueHolder\MethodGenerator\MagicGet;
use ProxyManager\ProxyGenerator\LazyLoadingValueHolder\MethodGenerator\MagicIsset;
use ProxyManager\ProxyGenerator\LazyLoadingValueHolder\MethodGenerator\MagicSet;
use ProxyManager\ProxyGenerator\LazyLoadingValueHolder\MethodGenerator\MagicSleep;
use ProxyManager\ProxyGenerator\LazyLoadingValueHolder\MethodGenerator\MagicUnset;
use ProxyManager\ProxyGenerator\LazyLoadingValueHolder\MethodGenerator\SetProxyInitializer;
use ProxyManager\ProxyGenerator\LazyLoadingValueHolder\PropertyGenerator\InitializerProperty;
use ProxyManager\ProxyGenerator\LazyLoadingValueHolder\PropertyGenerator\ValueHolderProperty;
use ProxyManager\ProxyGenerator\PropertyGenerator\PublicPropertiesMap;
use ProxyManager\ProxyGenerator\Util\Properties;
use ProxyManager\ProxyGenerator\Util\ProxiedMethodsFilter;
use ProxyManager\ProxyGenerator\ValueHolder\MethodGenerator\Constructor;
use ProxyManager\ProxyGenerator\ValueHolder\MethodGenerator\GetWrappedValueHolderValue;
use ReflectionClass;
use ReflectionMethod;

use function array_map;
use function array_merge;

/**
 * Generator for proxies implementing {@see \ProxyManager\Proxy\VirtualProxyInterface}
 *
 * {@inheritDoc}
 */
class LazyLoadingValueHolderGenerator implements ProxyGeneratorInterface
{
    /**
     * {@inheritDoc}
     *
     * @return void
     *
     * @throws InvalidProxiedClassException
     * @throws InvalidArgumentException
     */
    public function generate(ReflectionClass $originalClass, ClassGenerator $classGenerator)
    {
        CanProxyAssertion::assertClassCanBeProxied($originalClass);

        $interfaces       = [VirtualProxyInterface::class];
        $publicProperties = new PublicPropertiesMap(Properties::fromReflectionClass($originalClass));

        if ($originalClass->isInterface()) {
            $interfaces[] = $originalClass->getName();
        } else {
            $classGenerator->setExtendedClass($originalClass->getName());
        }

        $classGenerator->setImplementedInterfaces($interfaces);
        $classGenerator->addPropertyFromGenerator($valueHolder = new ValueHolderProperty($originalClass));
        $classGenerator->addPropertyFromGenerator($initializer = new InitializerProperty());
        $classGenerator->addPropertyFromGenerator($publicProperties);

        array_map(
            static function (MethodGenerator $generatedMethod) use ($originalClass, $classGenerator): void {
                ClassGeneratorUtils::addMethodIfNotFinal($originalClass, $classGenerator, $generatedMethod);
            },
            array_merge(
                array_map(
                    $this->buildLazyLoadingMethodInterceptor($initializer, $valueHolder),
                    ProxiedMethodsFilter::getProxiedMethods($originalClass)
                ),
                [
                    new StaticProxyConstructor($initializer, Properties::fromReflectionClass($originalClass)),
                    Constructor::generateMethod($originalClass, $valueHolder),
                    new MagicGet($originalClass, $initializer, $valueHolder, $publicProperties),
                    new MagicSet($originalClass, $initializer, $valueHolder, $publicProperties),
                    new MagicIsset($originalClass, $initializer, $valueHolder, $publicProperties),
                    new MagicUnset($originalClass, $initializer, $valueHolder, $publicProperties),
                    new MagicClone($originalClass, $initializer, $valueHolder),
                    new MagicSleep($originalClass, $initializer, $valueHolder),
                    new MagicWakeup($originalClass),
                    new SetProxyInitializer($initializer),
                    new GetProxyInitializer($initializer),
                    new InitializeProxy($initializer, $valueHolder),
                    new IsProxyInitialized($valueHolder),
                    new GetWrappedValueHolderValue($valueHolder),
                ]
            )
        );
    }

    private function buildLazyLoadingMethodInterceptor(
        InitializerProperty $initializer,
        ValueHolderProperty $valueHolder
    ): callable {
        return static fn (ReflectionMethod $method): LazyLoadingMethodInterceptor => LazyLoadingMethodInterceptor::generateMethod(
            new MethodReflection($method->getDeclaringClass()->getName(), $method->getName()),
            $initializer,
            $valueHolder
        );
    }
}
