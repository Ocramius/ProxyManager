<?php

declare(strict_types=1);

namespace ProxyManager\Proxy;

use Closure;

/**
 * Aggregates AccessInterceptor and ValueHolderInterface, mostly for return type hinting
 *
 * @psalm-template InterceptedObjectType of object
 */
interface AccessInterceptorValueHolderInterface extends AccessInterceptorInterface, ValueHolderInterface
{
    /**
     * {@inheritDoc}
     *
     * Definitions are duplicated here to allow templated definitions in this child type
     *
     * @psalm-param null|Closure(
     *   InterceptedObjectType&AccessInterceptorInterface=,
     *   InterceptedObjectType=,
     *   string=,
     *   array<string, mixed>=,
     *   bool=
     * ) : mixed $prefixInterceptor
     */
    public function setMethodPrefixInterceptor(string $methodName, ?Closure $prefixInterceptor = null) : void;

    /**
     * {@inheritDoc}
     *
     * Definitions are duplicated here to allow templated definitions in this child type
     *
     * @param string       $methodName        name of the intercepted method
     * @param Closure|null $suffixInterceptor interceptor closure or null to unset the currently active interceptor
     *
     * @psalm-param null|Closure(
     *   InterceptedObjectType&AccessInterceptorInterface=,
     *   InterceptedObjectType=,
     *   string=,
     *   array<string, mixed>=,
     *   mixed=,
     *   bool=
     * ) : mixed $suffixInterceptor
     */
    public function setMethodSuffixInterceptor(string $methodName, ?Closure $suffixInterceptor = null) : void;

    /**
     * {@inheritDoc}
     *
     * Definitions are duplicated here to allow templated definitions in this child type
     *
     * @psalm-return InterceptedObjectType|null
     */
    public function getWrappedValueHolderValue() : ?object;
}
