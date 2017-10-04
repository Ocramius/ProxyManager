<?php

declare(strict_types=1);

namespace ProxyManager\ProxyGenerator\AccessInterceptorScopeLocalizer\MethodGenerator;

use ProxyManager\Generator\MethodGenerator;
use ProxyManager\ProxyGenerator\AccessInterceptorScopeLocalizer\MethodGenerator\Util\InterceptorGenerator;
use Zend\Code\Generator\PropertyGenerator;
use Zend\Code\Reflection\MethodReflection;

/**
 * Method with additional pre- and post- interceptor logic in the body
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
class InterceptedMethod extends MethodGenerator
{
    /**
     * @param \Zend\Code\Reflection\MethodReflection $originalMethod
     * @param \Zend\Code\Generator\PropertyGenerator $prefixInterceptors
     * @param \Zend\Code\Generator\PropertyGenerator $suffixInterceptors
     *
     * @return self
     *
     * @throws \Zend\Code\Generator\Exception\InvalidArgumentException
     */
    public static function generateMethod(
        MethodReflection $originalMethod,
        PropertyGenerator $prefixInterceptors,
        PropertyGenerator $suffixInterceptors
    ) : self {
        /* @var $method self */
        $method          = static::fromReflection($originalMethod);
        $forwardedParams = [];

        foreach ($originalMethod->getParameters() as $parameter) {
            $forwardedParams[]   = ($parameter->isVariadic() ? '...' : '') . '$' . $parameter->getName();
        }

        $method->setDocBlock('{@inheritDoc}');
        $method->setBody(InterceptorGenerator::createInterceptedMethodBody(
            '$returnValue = parent::'
            . $originalMethod->getName() . '(' . implode(', ', $forwardedParams) . ');',
            $method,
            $prefixInterceptors,
            $suffixInterceptors,
            $originalMethod
        ));

        return $method;
    }
}
