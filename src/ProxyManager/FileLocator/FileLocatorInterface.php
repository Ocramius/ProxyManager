<?php

declare(strict_types=1);

namespace ProxyManager\FileLocator;

/**
 * Basic autoloader utilities required to work with proxy files
 */
interface FileLocatorInterface
{
    /**
     * Retrieves the file name for the given proxy
     */
    public function getProxyFileName(string $className) : string;
}
