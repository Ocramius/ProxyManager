<?php

namespace PHPSTORM_META {

    override(
        \ProxyManager\Factory\AccessInterceptorScopeLocalizerFactory::createProxy(0),
        map([
            '@&ProxyManager\Proxy\AccessInterceptorInterface',
        ])
    );

    override(
        \ProxyManager\Factory\AccessInterceptorValueHolderFactory::createProxy(0),
        map([
            '@&ProxyManager\Proxy\AccessInterceptorValueHolderInterface',
        ])
    );

    override(
        \ProxyManager\Factory\LazyLoadingGhostFactory::createProxy(0),
        map([
            '@&ProxyManager\Proxy\GhostObjectInterface',
        ])
    );

    override(
        \ProxyManager\Factory\LazyLoadingValueHolderFactory::createProxy(0),
        map([
            '@&ProxyManager\Proxy\VirtualProxyInterface',
        ])
    );

    override(
        \ProxyManager\Factory\NullObjectFactory::createProxy(0),
        map([
            '@&ProxyManager\Proxy\NullObjectInterface',
        ])
    );

    override(
        \ProxyManager\Factory\RemoteObjectFactory::createProxy(0),
        map([
            '@&ProxyManager\Proxy\RemoteObjectInterface',
        ])
    );
}
