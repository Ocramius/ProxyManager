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

You can learn about the proxy pattern and how to use the **ProxyManager** on the [online documentation](http://ocramius.github.io/ProxyManager).

## Installation

The suggested installation method is via [composer](https://getcomposer.org/):

```sh
php composer.phar require ocramius/proxy-manager:1.0.*
```

## Proxy example

Here's how you build a lazy loadable object with ProxyManager using a *Virtual Proxy*

```php
$factory = new \ProxyManager\Factory\LazyLoadingValueHolderFactory();

$proxy = $factory->createProxy(
    'MyApp\HeavyComplexObject',
    function (& $wrappedObject, $proxy, $method, $parameters, & $initializer) {
        $wrappedObject = new HeavyComplexObject(); // instantiation logic here
        $initializer   = null; // turning off further lazy initialization
    }
);

$proxy->doFoo();
```

See the [online documentation](http://ocramius.github.io/ProxyManager) for more supported proxy types and examples. 
