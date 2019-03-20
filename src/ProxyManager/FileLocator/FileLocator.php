<?php

declare(strict_types=1);

namespace ProxyManager\FileLocator;

use ProxyManager\Exception\InvalidProxyDirectoryException;
use const DIRECTORY_SEPARATOR;
use function realpath;
use function str_replace;

/**
 * {@inheritDoc}
 */
class FileLocator implements FileLocatorInterface
{
    protected string $proxiesDirectory;

    /**
     * @throws InvalidProxyDirectoryException
     */
    public function __construct(string $proxiesDirectory)
    {
        $absolutePath = realpath($proxiesDirectory);

        if ($absolutePath === false) {
            throw InvalidProxyDirectoryException::proxyDirectoryNotFound($proxiesDirectory);
        }

        $this->proxiesDirectory = $absolutePath;
    }

    /**
     * {@inheritDoc}
     */
    public function getProxyFileName(string $className) : string
    {
        return $this->proxiesDirectory . DIRECTORY_SEPARATOR . str_replace('\\', '', $className) . '.php';
    }
}
