<?php

declare(strict_types=1);

namespace ProxyManager\Proxy;

use Closure;

/**
 * Access interceptor object marker
 *
 * @psalm-template InterceptedObjectType of object
 */
interface AccessInterceptorInterface extends ProxyInterface
{
    /**
     * Set or remove the prefix interceptor for a method
     *
     * @link https://github.com/Ocramius/ProxyManager/blob/master/docs/access-interceptor-value-holder.md
     *
     * A prefix interceptor should have a signature like following:
     *
     * <code>
     * $interceptor = function ($proxy, $instance, string $method, array $params, & $returnEarly) {};
     * </code>
     *
     * @param string       $methodName        name of the intercepted method
     * @param Closure|null $prefixInterceptor interceptor closure or null to unset the currently active interceptor
     * @psalm-param null|Closure(
     *   InterceptedObjectType&AccessInterceptorInterface=,
     *   InterceptedObjectType=,
     *   string=,
     *   array<string, mixed>=,
     *   bool=
     * ) : mixed $prefixInterceptor
     */
    public function setMethodPrefixInterceptor(string $methodName, ?Closure $prefixInterceptor = null): void;

    /**
     * Set or remove the suffix interceptor for a method
     *
     * @link https://github.com/Ocramius/ProxyManager/blob/master/docs/access-interceptor-value-holder.md
     *
     * A prefix interceptor should have a signature like following:
     *
     * <code>
     * $interceptor = function ($proxy, $instance, string $method, array $params, $returnValue, & $returnEarly) {};
     * </code>
     *
     * @param string       $methodName        name of the intercepted method
     * @param Closure|null $suffixInterceptor interceptor closure or null to unset the currently active interceptor
     * @psalm-param null|Closure(
     *   InterceptedObjectType&AccessInterceptorInterface=,
     *   InterceptedObjectType=,
     *   string=,
     *   array<string, mixed>=,
     *   mixed=,
     *   bool=
     * ) : mixed $suffixInterceptor
     */
    public function setMethodSuffixInterceptor(string $methodName, ?Closure $suffixInterceptor = null): void;
}
