<?php

declare(strict_types=1);

namespace ProxyManager;

use Composer\InstalledVersions;
use OutOfBoundsException;

/**
 * Version class
 *
 * Note that we cannot check the version at runtime via `git` because that would cause a lot of I/O operations.
 */
final class Version
{
    /**
     * Private constructor - this class is not meant to be instantiated
     */
    private function __construct()
    {
    }

    /**
     * Retrieves the package version in the format <detected-version>@<commit-hash>,
     * where the detected version is what composer could detect.
     *
     * @throws OutOfBoundsException
     *
     * @psalm-pure
     *
     * @psalm-suppress MixedOperand `composer-runtime-api:^2` has rough if no type declarations at all - we'll live with it
     * @psalm-suppress ImpureMethodCall `composer-runtime-api:^2` has rough if no type declarations at all - we'll live with it
     */
    public static function getVersion(): string
    {
        return (string) InstalledVersions::getPrettyVersion('ocramius/proxy-manager')
            . '@' . (string) InstalledVersions::getReference('ocramius/proxy-manager');
    }
}
