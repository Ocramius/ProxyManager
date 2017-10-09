<?php

declare(strict_types=1);

namespace ProxyManager\Generator;

use ReflectionMethod;
use Zend\Code\Generator\MethodGenerator as ZendMethodGenerator;
use Zend\Code\Generator\ParameterGenerator;
use Zend\Code\Reflection\MethodReflection;

/**
 * Method generator that fixes minor quirks in ZF2's method generator
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
class MethodGenerator extends ZendMethodGenerator
{
    /**
     * {@inheritDoc}
     */
    public static function fromReflection(MethodReflection $reflectionMethod) : self
    {
        /* @var $method self */
        $method = self::fromReflectionWithoutDocBlock($reflectionMethod);

        $method->setInterface(false);

        return $method;
    }

    /**
     * @see \Zend\Code\Generator\MethodGenerator::fromReflection
     */
    private static function fromReflectionWithoutDocBlock(MethodReflection $reflectionMethod)
    {
        $method         = new static();
        $declaringClass = $reflectionMethod->getDeclaringClass();

        $method->setSourceContent($reflectionMethod->getContents(false));
        $method->setSourceDirty(false);
        $method->setReturnType(self::extractReturnTypeFromMethodReflection($reflectionMethod));

        $method->setFinal($reflectionMethod->isFinal());

        if ($reflectionMethod->isPrivate()) {
            $method->setVisibility(self::VISIBILITY_PRIVATE);
        } elseif ($reflectionMethod->isProtected()) {
            $method->setVisibility(self::VISIBILITY_PROTECTED);
        } else {
            $method->setVisibility(self::VISIBILITY_PUBLIC);
        }

        $method->setInterface($declaringClass->isInterface());
        $method->setStatic($reflectionMethod->isStatic());
        $method->setReturnsReference($reflectionMethod->returnsReference());
        $method->setName($reflectionMethod->getName());

        foreach ($reflectionMethod->getParameters() as $reflectionParameter) {
            $method->setParameter(ParameterGenerator::fromReflection($reflectionParameter));
        }

        $method->setBody(static::clearBodyIndention($reflectionMethod->getBody()));

        return $method;
    }

    /**
     * @see \Zend\Code\Generator\MethodGenerator::extractReturnTypeFromMethodReflection
     */
    private static function extractReturnTypeFromMethodReflection(MethodReflection $methodReflection)
    {
        $returnType = method_exists($methodReflection, 'getReturnType')
            ? $methodReflection->getReturnType()
            : null;

        if (! $returnType) {
            return null;
        }

        if (! method_exists($returnType, 'getName')) {
            return self::expandLiteralType((string) $returnType, $methodReflection);
        }

        return ($returnType->allowsNull() ? '?' : '')
            . self::expandLiteralType($returnType->getName(), $methodReflection);
    }

    /**
     * @see \Zend\Code\Generator\MethodGenerator::expandLiteralType
     */
    private static function expandLiteralType($literalReturnType, ReflectionMethod $methodReflection)
    {
        if ('self' === strtolower($literalReturnType)) {
            return $methodReflection->getDeclaringClass()->getName();
        }

        if ('parent' === strtolower($literalReturnType)) {
            return $methodReflection->getDeclaringClass()->getParentClass()->getName();
        }

        return $literalReturnType;
    }
}
