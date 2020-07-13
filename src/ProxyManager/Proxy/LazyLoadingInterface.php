<?php

declare(strict_types=1);

namespace ProxyManager\Proxy;

use Closure;

/**
 * Lazy loading object identifier
 *
 * @psalm-template LazilyLoadedObjectType of object
 */
interface LazyLoadingInterface extends ProxyInterface
{
    /**
     * Set or unset the initializer for the proxy instance
     *
     * @link https://github.com/Ocramius/ProxyManager/blob/master/docs/lazy-loading-value-holder.md#lazy-initialization
     *
     * An initializer should have a signature like following:
     *
     * <code>
     * $initializer = function (
     *   & ?object $wrappedObject,
     *   LazyLoadingInterface $proxy,
     *   string $calledMethod,
     *   array $callParameters,
     *   & ?\Closure $initializer,
     *   array $propertiesToBeSet = [] // works only on ghost objects
     * ) {};
     * </code>
     *
     * @return void
     *
     * @psalm-param null|Closure(
     *   LazilyLoadedObjectType|null=,
     *   LazilyLoadedObjectType&LazyLoadingInterface<LazilyLoadedObjectType>=,
     *   string=,
     *   array<string, mixed>=,
     *   ?Closure=,
     *   array<string, mixed>=
     * ) : bool $initializer
     */
    public function setProxyInitializer(?Closure $initializer = null);

    /**
     * @psalm-return null|Closure(
     *   LazilyLoadedObjectType|null=,
     *   LazilyLoadedObjectType&LazyLoadingInterface<LazilyLoadedObjectType>=,
     *   string,
     *   array<string, mixed>=,
     *   ?Closure=,
     *   array<string, mixed>=
     * ) : bool
     */
    public function getProxyInitializer(): ?Closure;

    /**
     * Force initialization of the proxy
     *
     * @return bool true if the proxy could be initialized
     */
    public function initializeProxy(): bool;

    /**
     * Retrieves current initialization status of the proxy
     */
    public function isProxyInitialized(): bool;
}
