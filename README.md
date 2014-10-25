# Proxy Manager

This library aims at providing abstraction for generating various kinds of [proxy classes](http://marco-pivetta.com/proxy-pattern-in-php/).

![ProxyManager](proxy-manager.png)

[![Build Status](https://travis-ci.org/Ocramius/ProxyManager.png?branch=master)](https://travis-ci.org/Ocramius/ProxyManager)
[![Code Coverage](https://scrutinizer-ci.com/g/Ocramius/ProxyManager/badges/coverage.png?s=ca3b9ceb9e36aeec0e57569cc8983394b7d2a59e)](https://scrutinizer-ci.com/g/Ocramius/ProxyManager/)
[![Scrutinizer Quality Score](https://scrutinizer-ci.com/g/Ocramius/ProxyManager/badges/quality-score.png?s=eaa858f876137ed281141b1d1e98acfa739729ed)](https://scrutinizer-ci.com/g/Ocramius/ProxyManager/)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/69fe5f97-b1c8-4ddd-93ce-900b8b788cf2/mini.png)](https://insight.sensiolabs.com/projects/69fe5f97-b1c8-4ddd-93ce-900b8b788cf2)
[![Dependency Status](https://www.versioneye.com/package/php--ocramius--proxy-manager/badge.png)](https://www.versioneye.com/package/php--ocramius--proxy-manager)
[![Reference Status](https://www.versioneye.com/php/ocramius:proxy-manager/reference_badge.svg)](https://www.versioneye.com/php/ocramius:proxy-manager/references)
[![HHVM Status](http://hhvm.h4cc.de/badge/ocramius/proxy-manager.png)](http://hhvm.h4cc.de/package/ocramius/proxy-manager)

[![Total Downloads](https://poser.pugx.org/ocramius/proxy-manager/downloads.png)](https://packagist.org/packages/ocramius/proxy-manager)
[![Latest Stable Version](https://poser.pugx.org/ocramius/proxy-manager/v/stable.png)](https://packagist.org/packages/ocramius/proxy-manager)
[![Latest Unstable Version](https://poser.pugx.org/ocramius/proxy-manager/v/unstable.png)](https://packagist.org/packages/ocramius/proxy-manager)


## Documentation

You can learn about proxy pattern and how use **ProxyManager** on [online documentation](http://ocramius.github.io/ProxyManager).

## Installation

The suggested installation method is via [composer](https://getcomposer.org/):

```sh
php composer.phar require ocramius/proxy-manager:1.0.*
```

## Proxy example

### Ghost Objects


Similar to value holder, a ghost object is usually created to handle lazy loading.

The difference between a value holder and a ghost object is that the ghost object does not contain a real instance of
the required object, but handles lazy loading by initializing its own inherited properties.

ProxyManager can generate [lazy loading ghost objects](http://www.martinfowler.com/eaaCatalog/lazyLoad.html),
which are proxies used to save performance and memory for large datasets and graphs representing relational data.
Ghost objects are particularly useful when building data-mappers.

Additionally, the overhead introduced by ghost objects is very low when compared to the memory and performance overhead
caused by virtual proxies.

```php
$factory = new \ProxyManager\Factory\LazyLoadingGhostFactory();

$proxy = $factory->createProxy(
    'MyApp\HeavyComplexObject',
    function ($proxy, $method, $parameters, & $initializer) {
        $initializer   = null; // turning off further lazy initialization

        // modify the proxy instance
        $proxy->setFoo('foo');
        $proxy->setBar('bar');

        return true;
    }
);

$proxy->doFoo();
```

See the [online documentation](http://ocramius.github.io/ProxyManager) for more proxies type and examples. 


## Contributing

Please read the [CONTRIBUTING.md](https://github.com/Ocramius/ProxyManager/blob/master/CONTRIBUTING.md) contents if you
wish to help out!

## Credits

The idea was originated by a [talk about Proxies in PHP OOP](http://marco-pivetta.com/proxy-pattern-in-php/) that I gave
at the [@phpugffm](https://twitter.com/phpugffm) in January 2013.