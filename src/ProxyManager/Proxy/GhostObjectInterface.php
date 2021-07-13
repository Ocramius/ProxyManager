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
     * @psalm-param null|Closure(
     *   LazilyLoadedObjectType&GhostObjectInterface<LazilyLoadedObjectType>=,
     *   string=,
     *   array<string, mixed>=,
     *   ?Closure=,
     *   array<string, mixed>=
     * ) : bool $initializer
     *
     * @psalm-suppress ImplementedParamTypeMismatch Note that the closure signature below is slightly different
     *                                              from the one declared in LazyLoadingInterface.
     */
    public function setProxyInitializer(?Closure $initializer = null);

    /**
     * {@inheritDoc}
     *
     * Definitions are duplicated here to allow templated definitions in this child type
     *
     * @psalm-return null|Closure(
     *   LazilyLoadedObjectType&GhostObjectInterface<LazilyLoadedObjectType>=,
     *   string,
     *   array<string, mixed>=,
     *   ?Closure=,
     *   array<string, mixed>=
     * ) : bool
     *
     * @psalm-suppress ImplementedReturnTypeMismatch Note that the closure signature below is slightly different
     *                                               from the one declared in LazyLoadingInterface.
     */
    public function getProxyInitializer(): ?Closure;
}
