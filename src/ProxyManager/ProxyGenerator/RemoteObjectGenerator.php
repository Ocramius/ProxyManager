<?php

declare(strict_types=1);

namespace ProxyManager\ProxyGenerator;

use Laminas\Code\Generator\ClassGenerator;
use Laminas\Code\Generator\Exception\InvalidArgumentException;
use Laminas\Code\Generator\MethodGenerator;
use Laminas\Code\Reflection\MethodReflection;
use ProxyManager\Exception\InvalidProxiedClassException;
use ProxyManager\Generator\Util\ClassGeneratorUtils;
use ProxyManager\Proxy\RemoteObjectInterface;
use ProxyManager\ProxyGenerator\Assertion\CanProxyAssertion;
use ProxyManager\ProxyGenerator\RemoteObject\MethodGenerator\MagicGet;
use ProxyManager\ProxyGenerator\RemoteObject\MethodGenerator\MagicIsset;
use ProxyManager\ProxyGenerator\RemoteObject\MethodGenerator\MagicSet;
use ProxyManager\ProxyGenerator\RemoteObject\MethodGenerator\MagicUnset;
use ProxyManager\ProxyGenerator\RemoteObject\MethodGenerator\RemoteObjectMethod;
use ProxyManager\ProxyGenerator\RemoteObject\MethodGenerator\StaticProxyConstructor;
use ProxyManager\ProxyGenerator\RemoteObject\PropertyGenerator\AdapterProperty;
use ProxyManager\ProxyGenerator\Util\ProxiedMethodsFilter;
use ReflectionClass;
use ReflectionMethod;

use function array_map;
use function array_merge;

/**
 * Generator for proxies implementing {@see \ProxyManager\Proxy\RemoteObjectInterface}
 *
 * {@inheritDoc}
 */
class RemoteObjectGenerator implements ProxyGeneratorInterface
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

        $interfaces = [RemoteObjectInterface::class];

        if ($originalClass->isInterface()) {
            $interfaces[] = $originalClass->getName();
        } else {
            $classGenerator->setExtendedClass($originalClass->getName());
        }

        $classGenerator->setImplementedInterfaces($interfaces);
        $classGenerator->addPropertyFromGenerator($adapter = new AdapterProperty());

        array_map(
            static function (MethodGenerator $generatedMethod) use ($originalClass, $classGenerator): void {
                ClassGeneratorUtils::addMethodIfNotFinal($originalClass, $classGenerator, $generatedMethod);
            },
            array_merge(
                array_map(
                    static function (ReflectionMethod $method) use ($adapter, $originalClass): RemoteObjectMethod {
                        return RemoteObjectMethod::generateMethod(
                            new MethodReflection($method->getDeclaringClass()->getName(), $method->getName()),
                            $adapter,
                            $originalClass
                        );
                    },
                    ProxiedMethodsFilter::getProxiedMethods(
                        $originalClass,
                        ['__get', '__set', '__isset', '__unset']
                    )
                ),
                [
                    new StaticProxyConstructor($originalClass, $adapter),
                    new MagicGet($originalClass, $adapter),
                    new MagicSet($originalClass, $adapter),
                    new MagicIsset($originalClass, $adapter),
                    new MagicUnset($originalClass, $adapter),
                ]
            )
        );
    }
}
