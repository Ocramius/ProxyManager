<?php

declare(strict_types=1);

namespace ProxyManager\Autoloader;

/**
 * Basic autoloader utilities required to work with proxy files
 */
interface AutoloaderInterface
{
    /**
     * Callback to allow the object to be handled as autoloader - tries to autoload the given class name
     *
     * @psalm-param class-string $className
     */
    public function __invoke(string $className): bool;
}
