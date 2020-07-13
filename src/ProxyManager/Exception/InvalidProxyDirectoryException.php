<?php

declare(strict_types=1);

namespace ProxyManager\Exception;

use InvalidArgumentException;

use function sprintf;

/**
 * Exception for invalid directories
 */
class InvalidProxyDirectoryException extends InvalidArgumentException implements ExceptionInterface
{
    public static function proxyDirectoryNotFound(string $directory): self
    {
        return new self(sprintf('Provided directory "%s" does not exist', $directory));
    }
}
