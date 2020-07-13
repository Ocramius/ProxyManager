<?php

declare(strict_types=1);

namespace ProxyManager\Generator\Util;

use function preg_match;
use function str_replace;
use function uniqid;

/**
 * Utility class capable of generating unique
 * valid class/property/method identifiers
 */
abstract class UniqueIdentifierGenerator
{
    public const VALID_IDENTIFIER_FORMAT = '/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]+$/';
    public const DEFAULT_IDENTIFIER      = 'g';

    /**
     * Generates a valid unique identifier from the given name
     *
     * @psalm-return class-string
     * @psalm-suppress MoreSpecificReturnType
     */
    public static function getIdentifier(string $name): string
    {
        /**
         * @psalm-suppress LessSpecificReturnStatement
         */
        return str_replace(
            '.',
            '',
            uniqid(
                preg_match(self::VALID_IDENTIFIER_FORMAT, $name)
                ? $name
                : self::DEFAULT_IDENTIFIER,
                true
            )
        );
    }
}
