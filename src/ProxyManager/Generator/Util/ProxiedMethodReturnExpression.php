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
        $originalReturnType = $originalMethod?->getReturnType();
        $expression         = null;

        if ($originalReturnType instanceof ReflectionNamedType) {
            $expression = self::generateExpressionForNamedType($originalReturnType, $returnedValueExpression);
        }

        return $expression ?? 'return ' . $returnedValueExpression . ';';
    }

    private static function generateExpressionForNamedType(ReflectionNamedType $originalReturnType, string $returnedValueExpression): ?string
    {
        $originalReturnTypeName = $originalReturnType->getName();
        if ($originalReturnTypeName === 'void') {
            return $returnedValueExpression . ";\nreturn;";
        }

        if ($originalReturnTypeName === 'never') {
            return $returnedValueExpression . ';';
        }

        return null;
    }
}
