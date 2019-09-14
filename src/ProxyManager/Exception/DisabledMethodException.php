<?php

declare(strict_types=1);

namespace ProxyManager\Exception;

use BadMethodCallException;
use function sprintf;

/**
 * Exception for forcefully disabled methods
 *
 * @psalm-immutable
 */
class DisabledMethodException extends BadMethodCallException implements ExceptionInterface
{
    public const NAME = self::class;

    public static function disabledMethod(string $method) : self
    {
        return new self(sprintf('Method "%s" is forcefully disabled', $method));
    }
}
