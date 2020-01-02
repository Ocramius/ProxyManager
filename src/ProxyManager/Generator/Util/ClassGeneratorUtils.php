<?php

declare(strict_types=1);

namespace ProxyManager\Generator\Util;

use Laminas\Code\Generator\ClassGenerator;
use Laminas\Code\Generator\MethodGenerator;
use ReflectionClass;

/**
 * Util class to help to generate code
 */
final class ClassGeneratorUtils
{
    public static function addMethodIfNotFinal(
        ReflectionClass $originalClass,
        ClassGenerator $classGenerator,
        MethodGenerator $generatedMethod
    ) : bool {
        $methodName = $generatedMethod->getName();

        if ($originalClass->hasMethod($methodName) && $originalClass->getMethod($methodName)->isFinal()) {
            return false;
        }

        $classGenerator->addMethodFromGenerator($generatedMethod);

        return true;
    }
}
