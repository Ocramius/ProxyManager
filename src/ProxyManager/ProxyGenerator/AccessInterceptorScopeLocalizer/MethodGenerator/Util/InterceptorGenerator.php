<?php

declare(strict_types=1);

namespace ProxyManager\ProxyGenerator\AccessInterceptorScopeLocalizer\MethodGenerator\Util;

use Laminas\Code\Generator\ParameterGenerator;
use Laminas\Code\Generator\PropertyGenerator;
use ProxyManager\Generator\MethodGenerator;
use ProxyManager\Generator\Util\ProxiedMethodReturnExpression;
use ReflectionMethod;
use function array_keys;
use function array_map;
use function implode;
use function str_replace;
use function var_export;

/**
 * Utility to create pre- and post- method interceptors around a given method body
 *
 * @private - this class is just here as a small utility for this component, don't use it in your own code
 */
class InterceptorGenerator
{
    private const TEMPLATE = <<<'PHP'
if (isset($this->{{$prefixInterceptorsName}}[{{$name}}])) {
    $returnEarly       = false;
    $prefixReturnValue = $this->{{$prefixInterceptorsName}}[{{$name}}]->__invoke($this, $this, {{$name}}, {{$paramsString}}, $returnEarly);

    if ($returnEarly) {
        {{$prefixEarlyReturnExpression}}
    }
}

{{$methodBody}}

if (isset($this->{{$suffixInterceptorsName}}[{{$name}}])) {
    $returnEarly       = false;
    $suffixReturnValue = $this->{{$suffixInterceptorsName}}[{{$name}}]->__invoke($this, $this, {{$name}}, {{$paramsString}}, $returnValue, $returnEarly);

    if ($returnEarly) {
        {{$suffixEarlyReturnExpression}}
    }
}

{{$returnExpression}}
PHP;

    /**
     * @param string $methodBody the body of the previously generated code.
     *                           It MUST assign the return value to a variable
     *                           `$returnValue` instead of directly returning
     */
    public static function createInterceptedMethodBody(
        string $methodBody,
        MethodGenerator $method,
        PropertyGenerator $prefixInterceptors,
        PropertyGenerator $suffixInterceptors,
        ?ReflectionMethod $originalMethod
    ) : string {
        $replacements = [
            '{{$name}}'                        => var_export($method->getName(), true),
            '{{$prefixInterceptorsName}}'      => $prefixInterceptors->getName(),
            '{{$prefixEarlyReturnExpression}}' => ProxiedMethodReturnExpression::generate('$prefixReturnValue', $originalMethod),
            '{{$methodBody}}'                  => $methodBody,
            '{{$suffixInterceptorsName}}'      => $suffixInterceptors->getName(),
            '{{$suffixEarlyReturnExpression}}' => ProxiedMethodReturnExpression::generate('$suffixReturnValue', $originalMethod),
            '{{$returnExpression}}'            => ProxiedMethodReturnExpression::generate('$returnValue', $originalMethod),
            '{{$paramsString}}'                => 'array(' . implode(', ', array_map(static function (ParameterGenerator $parameter) : string {
                return var_export($parameter->getName(), true) . ' => $' . $parameter->getName();
            }, $method->getParameters())) . ')',
        ];

        return str_replace(
            array_keys($replacements),
            $replacements,
            self::TEMPLATE
        );
    }
}
