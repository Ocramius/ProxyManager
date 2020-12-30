<?php

declare(strict_types=1);

namespace ProxyManager\Generator\Util;

use ReflectionMethod;
use ReflectionNamedType;

/**
 * Utility class to generate return expressions in method, given a method signature.
 *
 * This is required since return expressions may be forbidden by the method signature (void).
 */
final class ProxiedMethodReturnExpression
{
    public static function generate(string $returnedValueExpression, ?ReflectionMethod $originalMethod): string
    {
        $originalReturnType = $originalMethod === null
            ? null
            : $originalMethod->getReturnType();

        if ($originalReturnType instanceof ReflectionNamedType && $originalReturnType->getName() === 'void') {
            return $returnedValueExpression . ";\nreturn;";
        }

        return 'return ' . $returnedValueExpression . ';';
    }
}
