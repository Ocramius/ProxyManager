<?php

declare(strict_types=1);

namespace ProxyManager\Exception;

use LogicException;
use ProxyManager\ProxyGenerator\Util\Properties;
use ReflectionClass;
use ReflectionProperty;
use function array_map;
use function implode;
use function sprintf;

/**
 * Exception for invalid proxied classes
 */
class UnsupportedProxiedClassException extends LogicException implements ExceptionInterface
{
    public static function unsupportedLocalizedReflectionProperty(ReflectionProperty $property) : self
    {
        return new self(
            sprintf(
                'Provided reflection property "%s" of class "%s" is private and cannot be localized in PHP 5.3',
                $property->getName(),
                $property->getDeclaringClass()->getName()
            )
        );
    }

    public static function nonReferenceableLocalizedReflectionProperties(
        ReflectionClass $class,
        Properties $properties
    ) : self {
        return new self(sprintf(
            'Cannot create references for following properties of class %s: %s',
            $class->getName(),
            implode(', ', array_map(static function (ReflectionProperty $property) : string {
                return $property->getName();
            }, $properties->getInstanceProperties()))
        ));
    }
}
