<?php

declare(strict_types=1);

namespace ProxyManager\Inflector;

use ProxyManager\Proxy\ProxyInterface;

/**
 * Interface for a proxy- to user-class and user- to proxy-class name inflector
 */
interface ClassNameInflectorInterface
{
    /**
     * Marker for proxy classes - classes containing this marker are considered proxies
     */
    public const PROXY_MARKER = '__PM__';

    /**
     * Retrieve the class name of a user-defined class
     *
     * @psalm-param class-string<RealClassName>|class-string<ProxyInterface<RealClassName>> $className
     *
     * @psalm-return class-string<RealClassName>
     *
     * @psalm-template RealClassName of object
     */
    public function getUserClassName(string $className): string;

    /**
     * Retrieve the class name of the proxy for the given user-defined class name
     *
     * @param array<string, mixed> $options arbitrary options to be used for the generated class name
     * @psalm-param class-string<RealClassName>|class-string<ProxyInterface<RealClassName>> $className
     *
     * @psalm-return class-string<RealClassName&ProxyInterface>
     *
     * @psalm-template RealClassName of object
     */
    public function getProxyClassName(string $className, array $options = []): string;

    /**
     * Retrieve whether the provided class name is a proxy
     *
     * @psalm-param class-string<RealClassName>|class-string<ProxyInterface<RealClassName>> $className
     *
     * @psalm-template RealClassName of object
     */
    public function isProxyClassName(string $className): bool;
}
