<?php

declare(strict_types=1);

namespace ProxyManager\Proxy;

use Closure;

/**
 * Ghost object marker
 *
 * @psalm-template LazilyLoadedObjectType of object
 */
interface GhostObjectInterface extends LazyLoadingInterface
{
    /**
     * {@inheritDoc}
     *
     * Definitions are duplicated here to allow templated definitions in this child type.
     *
     * @psalm-suppress ImplementedParamTypeMismatch Note that the closure signature below is slightly different
     *                                              from the one declared in LazyLoadingInterface.
     * @psalm-param null|Closure(
     *   LazilyLoadedObjectType&GhostObjectInterface<LazilyLoadedObjectType>=,
     *   string=,
     *   array<string, mixed>=,
     *   ?Closure=,
     *   array<string, mixed>=
     * ) : bool $initializer
     */
    public function setProxyInitializer(?Closure $initializer = null);

    /**
     * {@inheritDoc}
     *
     * Definitions are duplicated here to allow templated definitions in this child type
     *
     * @psalm-suppress ImplementedReturnTypeMismatch Note that the closure signature below is slightly different
     *                                               from the one declared in LazyLoadingInterface.
     * @psalm-return null|Closure(
     *   LazilyLoadedObjectType&GhostObjectInterface<LazilyLoadedObjectType>=,
     *   string,
     *   array<string, mixed>=,
     *   ?Closure=,
     *   array<string, mixed>=
     * ) : bool
     */
    public function getProxyInitializer(): ?Closure;
}
