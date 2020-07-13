<?php

declare(strict_types=1);

namespace ProxyManager\ProxyGenerator;

use InvalidArgumentException;
use Laminas\Code\Generator\ClassGenerator;
use Laminas\Code\Generator\MethodGenerator;
use Laminas\Code\Reflection\MethodReflection;
use ProxyManager\Exception\InvalidProxiedClassException;
use ProxyManager\Generator\MethodGenerator as ProxyManagerMethodGenerator;
use ProxyManager\Generator\Util\ClassGeneratorUtils;
use ProxyManager\Proxy\GhostObjectInterface;
use ProxyManager\ProxyGenerator\Assertion\CanProxyAssertion;
use ProxyManager\ProxyGenerator\LazyLoading\MethodGenerator\StaticProxyConstructor;
use ProxyManager\ProxyGenerator\LazyLoadingGhost\MethodGenerator\CallInitializer;
use ProxyManager\ProxyGenerator\LazyLoadingGhost\MethodGenerator\GetProxyInitializer;
use ProxyManager\ProxyGenerator\LazyLoadingGhost\MethodGenerator\InitializeProxy;
use ProxyManager\ProxyGenerator\LazyLoadingGhost\MethodGenerator\IsProxyInitialized;
use ProxyManager\ProxyGenerator\LazyLoadingGhost\MethodGenerator\MagicClone;
use ProxyManager\ProxyGenerator\LazyLoadingGhost\MethodGenerator\MagicGet;
use ProxyManager\ProxyGenerator\LazyLoadingGhost\MethodGenerator\MagicIsset;
use ProxyManager\ProxyGenerator\LazyLoadingGhost\MethodGenerator\MagicSet;
use ProxyManager\ProxyGenerator\LazyLoadingGhost\MethodGenerator\MagicSleep;
use ProxyManager\ProxyGenerator\LazyLoadingGhost\MethodGenerator\MagicUnset;
use ProxyManager\ProxyGenerator\LazyLoadingGhost\MethodGenerator\SetProxyInitializer;
use ProxyManager\ProxyGenerator\LazyLoadingGhost\PropertyGenerator\InitializationTracker;
use ProxyManager\ProxyGenerator\LazyLoadingGhost\PropertyGenerator\InitializerProperty;
use ProxyManager\ProxyGenerator\LazyLoadingGhost\PropertyGenerator\PrivatePropertiesMap;
use ProxyManager\ProxyGenerator\LazyLoadingGhost\PropertyGenerator\ProtectedPropertiesMap;
use ProxyManager\ProxyGenerator\PropertyGenerator\PublicPropertiesMap;
use ProxyManager\ProxyGenerator\Util\Properties;
use ProxyManager\ProxyGenerator\Util\ProxiedMethodsFilter;
use ReflectionClass;
use ReflectionMethod;

use function array_map;
use function array_merge;

/**
 * Generator for proxies implementing {@see \ProxyManager\Proxy\GhostObjectInterface}
 *
 * {@inheritDoc}
 */
class LazyLoadingGhostGenerator implements ProxyGeneratorInterface
{
    /**
     * {@inheritDoc}
     *
     * @return void
     *
     * @throws InvalidProxiedClassException
     * @throws InvalidArgumentException
     *
     * @psalm-param array{skippedProperties?: array<int, string>} $proxyOptions
     */
    public function generate(ReflectionClass $originalClass, ClassGenerator $classGenerator, array $proxyOptions = [])
    {
        CanProxyAssertion::assertClassCanBeProxied($originalClass, false);

        $filteredProperties = Properties::fromReflectionClass($originalClass)
            ->filter($proxyOptions['skippedProperties'] ?? []);

        $publicProperties    = new PublicPropertiesMap($filteredProperties);
        $privateProperties   = new PrivatePropertiesMap($filteredProperties);
        $protectedProperties = new ProtectedPropertiesMap($filteredProperties);

        $classGenerator->setExtendedClass($originalClass->getName());
        $classGenerator->setImplementedInterfaces([GhostObjectInterface::class]);
        $classGenerator->addPropertyFromGenerator($initializer           = new InitializerProperty());
        $classGenerator->addPropertyFromGenerator($initializationTracker = new InitializationTracker());
        $classGenerator->addPropertyFromGenerator($publicProperties);
        $classGenerator->addPropertyFromGenerator($privateProperties);
        $classGenerator->addPropertyFromGenerator($protectedProperties);

        $init = new CallInitializer($initializer, $initializationTracker, $filteredProperties);

        array_map(
            static function (MethodGenerator $generatedMethod) use ($originalClass, $classGenerator): void {
                ClassGeneratorUtils::addMethodIfNotFinal($originalClass, $classGenerator, $generatedMethod);
            },
            array_merge(
                $this->getAbstractProxiedMethods($originalClass),
                [
                    $init,
                    new StaticProxyConstructor($initializer, $filteredProperties),
                    new MagicGet(
                        $originalClass,
                        $initializer,
                        $init,
                        $publicProperties,
                        $protectedProperties,
                        $privateProperties,
                        $initializationTracker
                    ),
                    new MagicSet(
                        $originalClass,
                        $initializer,
                        $init,
                        $publicProperties,
                        $protectedProperties,
                        $privateProperties
                    ),
                    new MagicIsset(
                        $originalClass,
                        $initializer,
                        $init,
                        $publicProperties,
                        $protectedProperties,
                        $privateProperties
                    ),
                    new MagicUnset(
                        $originalClass,
                        $initializer,
                        $init,
                        $publicProperties,
                        $protectedProperties,
                        $privateProperties
                    ),
                    new MagicClone($originalClass, $initializer, $init),
                    new MagicSleep($originalClass, $initializer, $init),
                    new SetProxyInitializer($initializer),
                    new GetProxyInitializer($initializer),
                    new InitializeProxy($initializer, $init),
                    new IsProxyInitialized($initializer),
                ]
            )
        );
    }

    /**
     * Retrieves all abstract methods to be proxied
     *
     * @return MethodGenerator[]
     */
    private function getAbstractProxiedMethods(ReflectionClass $originalClass): array
    {
        return array_map(
            static function (ReflectionMethod $method): ProxyManagerMethodGenerator {
                $generated = ProxyManagerMethodGenerator::fromReflectionWithoutBodyAndDocBlock(
                    new MethodReflection($method->getDeclaringClass()->getName(), $method->getName())
                );

                $generated->setAbstract(false);

                return $generated;
            },
            ProxiedMethodsFilter::getAbstractProxiedMethods($originalClass)
        );
    }
}
