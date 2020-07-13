<?php

declare(strict_types=1);

namespace ProxyManager\Exception;

use InvalidArgumentException;
use ReflectionClass;
use ReflectionMethod;

use function array_filter;
use function array_map;
use function implode;
use function sprintf;

/**
 * Exception for invalid proxied classes
 */
class InvalidProxiedClassException extends InvalidArgumentException implements ExceptionInterface
{
    public static function interfaceNotSupported(ReflectionClass $reflection): self
    {
        return new self(sprintf('Provided interface "%s" cannot be proxied', $reflection->getName()));
    }

    public static function finalClassNotSupported(ReflectionClass $reflection): self
    {
        return new self(sprintf('Provided class "%s" is final and cannot be proxied', $reflection->getName()));
    }

    public static function abstractProtectedMethodsNotSupported(ReflectionClass $reflection): self
    {
        return new self(sprintf(
            'Provided class "%s" has following protected abstract methods, and therefore cannot be proxied:' . "\n%s",
            $reflection->getName(),
            implode(
                "\n",
                array_map(
                    static function (ReflectionMethod $reflectionMethod): string {
                        return $reflectionMethod->getDeclaringClass()->getName() . '::' . $reflectionMethod->getName();
                    },
                    array_filter(
                        $reflection->getMethods(),
                        static function (ReflectionMethod $method): bool {
                            return $method->isAbstract() && $method->isProtected();
                        }
                    )
                )
            )
        ));
    }
}
