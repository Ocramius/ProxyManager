<?php

declare(strict_types=1);

namespace ProxyManager\ProxyGenerator;

use Laminas\Code\Generator\ClassGenerator;
use Laminas\Code\Generator\Exception\InvalidArgumentException;
use Laminas\Code\Reflection\MethodReflection;
use ProxyManager\Exception\InvalidProxiedClassException;
use ProxyManager\Generator\Util\ClassGeneratorUtils;
use ProxyManager\Proxy\NullObjectInterface;
use ProxyManager\ProxyGenerator\Assertion\CanProxyAssertion;
use ProxyManager\ProxyGenerator\NullObject\MethodGenerator\NullObjectMethodInterceptor;
use ProxyManager\ProxyGenerator\NullObject\MethodGenerator\StaticProxyConstructor;
use ProxyManager\ProxyGenerator\Util\ProxiedMethodsFilter;
use ReflectionClass;

/**
 * Generator for proxies implementing {@see \ProxyManager\Proxy\NullObjectInterface}
 *
 * {@inheritDoc}
 */
class NullObjectGenerator implements ProxyGeneratorInterface
{
    /**
     * {@inheritDoc}
     *
     * @throws InvalidProxiedClassException
     * @throws InvalidArgumentException
     */
    public function generate(ReflectionClass $originalClass, ClassGenerator $classGenerator): void
    {
        CanProxyAssertion::assertClassCanBeProxied($originalClass);

        $interfaces = [NullObjectInterface::class];

        if ($originalClass->isInterface()) {
            $interfaces[] = $originalClass->getName();
        } else {
            $classGenerator->setExtendedClass($originalClass->getName());
        }

        $classGenerator->setImplementedInterfaces($interfaces);

        foreach (ProxiedMethodsFilter::getProxiedMethods($originalClass, []) as $method) {
            $classGenerator->addMethodFromGenerator(
                NullObjectMethodInterceptor::generateMethod(
                    new MethodReflection($method->getDeclaringClass()->getName(), $method->getName())
                )
            );
        }

        ClassGeneratorUtils::addMethodIfNotFinal(
            $originalClass,
            $classGenerator,
            new StaticProxyConstructor($originalClass)
        );
    }
}
