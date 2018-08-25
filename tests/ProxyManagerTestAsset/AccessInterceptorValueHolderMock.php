<?php

declare(strict_types=1);

namespace ProxyManagerTestAsset;

use ProxyManager\Proxy\AccessInterceptorValueHolderInterface;

/**
 * Base test class to catch instantiations of access interceptor value holders
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
class AccessInterceptorValueHolderMock implements AccessInterceptorValueHolderInterface
{
    /**
     * @var object
     */
    public $instance;

    /**
     * @var callable[]
     */
    public $prefixInterceptors;

    /**
     * @var callable[]
     */
    public $suffixInterceptors;

    /**
     * @param object     $instance
     * @param callable[] $prefixInterceptors
     * @param callable[] $suffixInterceptors
     */
    public static function staticProxyConstructor($instance, $prefixInterceptors, $suffixInterceptors) : self
    {
        $selfInstance = new static(); // note: static because on-the-fly generated classes in tests extend this one.

        $selfInstance->instance           = $instance;
        $selfInstance->prefixInterceptors = $prefixInterceptors;
        $selfInstance->suffixInterceptors = $suffixInterceptors;

        return $selfInstance;
    }

    public function setMethodPrefixInterceptor(string $methodName, \Closure $prefixInterceptor = null) : void
    {
        // no-op (on purpose)
    }

    public function setMethodSuffixInterceptor(string $methodName, \Closure $suffixInterceptor = null) : void
    {
        // no-op (on purpose)
    }

    public function getWrappedValueHolderValue() : ?object
    {
        return $this->instance;
    }
}
