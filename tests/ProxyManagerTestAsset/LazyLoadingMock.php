<?php

declare(strict_types=1);

namespace ProxyManagerTestAsset;

use BadMethodCallException;
use ProxyManager\Proxy\GhostObjectInterface;
use ProxyManager\Proxy\VirtualProxyInterface;

/**
 * Base test class to catch instantiations of lazy loading objects
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
class LazyLoadingMock implements VirtualProxyInterface, GhostObjectInterface
{
    /**
     * @var callable
     */
    public $initializer;

    /**
     * @param callable $initializer
     *
     * @return static
     */
    public static function staticProxyConstructor($initializer) : self
    {
        $instance = new static();

        $instance->initializer = $initializer;

        return $instance;
    }

    /**
     * {@inheritDoc}
     */
    public function setProxyInitializer(\Closure $initializer = null)
    {
        $this->initializer = $initializer;
    }

    /**
     * {@inheritDoc}
     */
    public function getProxyInitializer()
    {
        return $this->initializer;
    }

    /**
     * {@inheritDoc}
     */
    public function initializeProxy() : bool
    {
        // empty (on purpose)
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function isProxyInitialized() : bool
    {
        return true;
    }

    /**
     * {@inheritDoc}
     *
     * @throws BadMethodCallException
     */
    public function getWrappedValueHolderValue()
    {
        // we're not supposed to call this
        throw new BadMethodCallException('Not implemented');
    }
}
