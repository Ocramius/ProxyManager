<?php

declare(strict_types=1);

namespace ProxyManager\ProxyGenerator\LazyLoadingValueHolder\MethodGenerator;

use Laminas\Code\Generator\Exception\InvalidArgumentException;
use Laminas\Code\Generator\PropertyGenerator;
use Laminas\Code\Reflection\MethodReflection;
use ProxyManager\Generator\MethodGenerator;
use ProxyManager\Generator\Util\ProxiedMethodReturnExpression;
use function implode;
use function var_export;

/**
 * Method decorator for lazy loading value holder objects
 */
class LazyLoadingMethodInterceptor extends MethodGenerator
{
    /**
     * @throws InvalidArgumentException
     */
    public static function generateMethod(
        MethodReflection $originalMethod,
        PropertyGenerator $initializerProperty,
        PropertyGenerator $valueHolderProperty
    ) : self {
        /** @var self $method */
        $method            = static::fromReflectionWithoutBodyAndDocBlock($originalMethod);
        $initializerName   = $initializerProperty->getName();
        $valueHolderName   = $valueHolderProperty->getName();
        $parameters        = $originalMethod->getParameters();
        $methodName        = $originalMethod->getName();
        $initializerParams = [];
        $forwardedParams   = [];

        foreach ($parameters as $parameter) {
            $parameterName       = $parameter->getName();
            $variadicPrefix      = $parameter->isVariadic() ? '...' : '';
            $initializerParams[] = var_export($parameterName, true) . ' => $' . $parameterName;
            $forwardedParams[]   = $variadicPrefix . '$' . $parameterName;
        }

        $method->setBody(
            '$this->' . $initializerName
            . ' && $this->' . $initializerName
            . '->__invoke($this->' . $valueHolderName . ', $this, ' . var_export($methodName, true)
            . ', array(' . implode(', ', $initializerParams) . '), $this->' . $initializerName . ");\n\n"
            . ProxiedMethodReturnExpression::generate(
                '$this->' . $valueHolderName . '->' . $methodName . '(' . implode(', ', $forwardedParams) . ')',
                $originalMethod
            )
        );

        return $method;
    }
}
