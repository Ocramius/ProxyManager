---
title: Changelog
---

This is a list of changes/improvements that were introduced in ProxyManager

## 2.0.0


### New features

#### PHP 7 support

ProxyManager will now correctly operate in PHP 7 environments.

#### PHP 7 Return type hints

ProxyManager will now correctly mimic signatures of methods with return type hints:

```php
class SayHello
{
    public function hello() : string
    {
        return 'hello!';
    }
}
```

#### PHP 7 Scalar type hints

ProxyManager will now correctly mimic signatures of methods with scalar type hints

```php
class SayHello
{
    public function hello(string $name) : string
    {
        return 'hello, ' . $name;
    }
}
```

#### PHP 5.6 Variadics support

ProxyManager will now correctly mimic behavior of methods with variadic parameters:

```php
class SayHello
{
    public function hello(string ...$names) : string
    {
        return 'hello, ' . implode(', ', $names);
    }
}
```

By-ref variadic arguments are also supported:

```php
class SayHello
{
    public function hello(string ... & $names)
    {
        foreach ($names as & $name) {
            $name = 'hello, ' . $name;
        }
    }
}
```

#### Constructors in proxies are not replaced anymore

In ProxyManager v1.x, the constructor of a proxy was completely replaced with a method
accepting proxy-specific parameters.

This is no longer true, and you will be able to use the constructor of your objects as
if the class wasn't proxied at all:

```php
class SayHello
{
    public function __construct()
    {
        echo 'Hello!';
    }
}

/* @var $proxyGenerator \ProxyManager\ProxyGenerator\ProxyGeneratorInterface */
$proxyClass = $proxyGenerator->generateProxy(
    new ReflectionClass(SayHello::class),
    new ClassGenerator('ProxyClassName')
);

eval('<?php ' . $proxyClass->generate());

$proxyName = $proxyClass->getName();
$object = new ProxyClassName(); // echoes "Hello!"

var_dump($object); // a proxy object
```

If you still want to manually build a proxy (without factories), a
`public static staticProxyConstructor` method is added to the generated proxy classes.

#### Friend classes support

You can now access state of "friend objects" at any time.

```php
class EmailAddress
{
    private $address;

    public function __construct(string $address)
    {
        assertEmail($address);
        
        $this->address = $address;
    }
    
    public function equalsTo(EmailAddress $other)
    {
        return $this->address === $other->address;
    }
}
```

When using lazy-loading or access-interceptors, the `equalsTo` method will
properly work, as even `protected` and `private` access are now correctly proxied.
