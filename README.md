# Proxy Manager

## A message to Russian 🇷🇺 people

If you currently live in Russia, please read [this message](./ToRussianPeople.md).

## Purpose

This library aims to provide abstraction for generating various kinds of 
  [proxy classes](http://ocramius.github.io/presentations/proxy-pattern-in-php/).

![ProxyManager](https://raw.githubusercontent.com/Ocramius/ProxyManager/917bf1698243a1079aaa27ed8ea08c2aef09f4cb/proxy-manager.png)

[![Mutation testing badge](https://img.shields.io/endpoint?style=flat&url=https%3A%2F%2Fbadge-api.stryker-mutator.io%2Fgithub.com%2FOcramius%2FProxyManager%2Fmaster)](https://dashboard.stryker-mutator.io/reports/github.com/Ocramius/ProxyManager/master)
[![Type Coverage](https://shepherd.dev/github/Ocramius/ProxyManager/coverage.svg)](https://shepherd.dev/github/Ocramius/ProxyManager)

[![Total Downloads](https://poser.pugx.org/ocramius/proxy-manager/downloads.png)](https://packagist.org/packages/ocramius/proxy-manager)
[![Latest Stable Version](https://poser.pugx.org/ocramius/proxy-manager/v/stable.png)](https://packagist.org/packages/ocramius/proxy-manager)
[![Latest Unstable Version](https://poser.pugx.org/ocramius/proxy-manager/v/unstable.png)](https://packagist.org/packages/ocramius/proxy-manager)


## Documentation

You can learn about the proxy pattern and how to use the **ProxyManager** in the [docs](docs).

## ocramius/proxy-manager for enterprise

Available as part of the Tidelift Subscription.

The maintainer of ocramius/proxy-manager and thousands of other packages are working with Tidelift to deliver commercial support and maintenance for the open source dependencies you use to build your applications. Save time, reduce risk, and improve code health, while paying the maintainers of the exact dependencies you use. [Learn more.](https://tidelift.com/subscription/pkg/packagist-ocramius-proxy-manager?utm_source=packagist-ocramius-proxy-manager&utm_medium=referral&utm_campaign=enterprise&utm_term=repo).

You can also contact the maintainer at ocramius@gmail.com for looking into issues related to this package
in your private projects.

## Installation

The suggested installation method is via [composer](https://getcomposer.org/):

```sh
php composer.phar require ocramius/proxy-manager
```

## Proxy example

Here's how you build a lazy loadable object with ProxyManager using a *Virtual Proxy*

```php
$factory = new \ProxyManager\Factory\LazyLoadingValueHolderFactory();

$proxy = $factory->createProxy(
    \MyApp\HeavyComplexObject::class,
    function (& $wrappedObject, $proxy, $method, $parameters, & $initializer) {
        $wrappedObject = new \MyApp\HeavyComplexObject(); // instantiation logic here
        $initializer   = null; // turning off further lazy initialization

        return true; // report success
    }
);

$proxy->doFoo();
```

See the [documentation](docs) for more supported proxy types and examples. 
