<?php

declare(strict_types=1);

namespace ProxyManager\Signature\Exception;

use ReflectionClass;
use UnexpectedValueException;

/**
 * Exception for invalid provided signatures
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
class InvalidSignatureException extends UnexpectedValueException implements ExceptionInterface
{
    /**
     * @param ReflectionClass $class
     * @param array           $parameters
     * @param string          $signature
     * @param string          $expected
     *
     * @return self
     */
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
