<?php

declare(strict_types=1);

namespace ProxyManager\Signature\Exception;

use ReflectionClass;
use UnexpectedValueException;
use function count;
use function sprintf;

/**
 * Exception for invalid provided signatures
 */
class InvalidSignatureException extends UnexpectedValueException implements ExceptionInterface
{
    /** @param mixed[] $parameters */
    public static function fromInvalidSignature(
        ReflectionClass $class,
        array $parameters,
        string $signature,
        string $expected
    ) : self {
        return new self(sprintf(
            'Found signature "%s" for class "%s" does not correspond to expected signature "%s" for %d parameters',
            $signature,
            $class->getName(),
            $expected,
            count($parameters)
        ));
    }
}
