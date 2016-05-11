---
title: Changelog
---

This is a list of changes/improvements that were introduced in ProxyManager

## 2.0.1

### Fixed

- Travis-CI environment was fixed to test the library using the minimum dependencies version.

### Added

- Added unit test to make sure that properties skipped should be preserved even being cloned.

## 2.0.0

### BC Breaks

Please refer to [the upgrade documentation](UPGRADE.md) to see which backwards-incompatible
changes were applied to this release.

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

#### Ghost objects now only lazy-load on state-access

Lazy loading ghost objects now trigger lazy-loading only when their state is accessed.
This also implies that lazy loading ghost objects cannot be used with interfaces anymore.

```php
class AccessPolicy
{
    private $policyName;
    
    /**
     * Calling this method WILL cause lazy-loading, when using a ghost object,
     * as the method is accessing the object's state
     */
    public function getPolicyName() : string
    {
        return $this->policyName;        
    }
    
    /**
     * Calling this method WILL NOT cause lazy-loading, when using a ghost object,
     * as the method is not reading any from the object.
     */
    public function allowAccess() : bool
    {
        return false;
    }
}
```

#### Faster ghost object state initialization

Lazy loading ghost objects can now be initialized in a more efficient way, by avoiding
reflection or setters:

```php
class Foo
{
    private $a;
    protected $b;
    public $c;
}

$factory = new \ProxyManager\Factory\LazyLoadingGhostFactory();

$proxy = $factory-createProxy(
    Foo::class,
    function (
        GhostObjectInterface $proxy, 
        string $method, 
        array $parameters, 
        & $initializer,
        array $properties
    ) {
        $initializer   = null;

        $properties["\0Foo\0a"] = 'abc';
        $properties["\0*\0b"]   = 'def';
        $properties['c']        = 'ghi';

        return true;
    }
);


$reflectionA = new ReflectionProperty(Foo::class, 'a');
$reflectionA->setAccessible(true);

var_dump($reflectionA->getValue($proxy)); // dumps "abc"

$reflectionB = new ReflectionProperty(Foo::class, 'b');
$reflectionB->setAccessible(true);

var_dump($reflectionB->getValue($proxy)); // dumps "def"

var_dump($proxy->c); // dumps "ghi"
```

#### Skipping lazy-loaded properties in generated proxies

Lazy loading ghost objects can now skip lazy-loading for certain properties.
This is especially useful when you have properties that are always available,
such as identifiers of entities:

```php
class User
{
    private $id;
    private $username;

    public function getId() : int
    {
        return $this->id;
    }

    public function getUsername() : string
    {
        return $this->username;
    }
}

/* @var $proxy User */
$proxy = (new \ProxyManager\Factory\LazyLoadingGhostFactory())->createProxy(
    User::class,
    function (
        GhostObjectInterface $proxy,
        string $method,
        array $parameters,
        & $initializer,
        array $properties
    ) {
        $initializer   = null;

        var_dump('Triggered lazy-loading!');

        $properties["\0User\0username"] = 'Ocramius';

        return true;
    },
    [
        'skippedProperties' => [
            "\0User\0id",
        ],
    ]
);

$idReflection = new \ReflectionProperty(User::class, 'id');

$idReflection->setAccessible(true);
$idReflection->setValue($proxy, 123);

var_dump($proxy->getId());       // 123
var_dump($proxy->getUsername()); // "Triggered lazy-loading!", then "Ocramius"
```

#### Proxies are now always generated on-the-fly by default

Proxies are now automatically generated any time you require them: no configuration
needed. If you want to gain better performance, you may still want to read
the [tuning for production docs](docs/tuning-for-production.md).

#### Proxy names are now hashed, simplified signature is attached to them

Proxy classes now have shorter names, as the parameters used to generate them are
hashed into their name. A signature is attached to proxy classes (as a private static
property) so that proxy classes aren't re-used across library updates.
Upgrading ProxyManager will now cause all proxies to be re-generated automatically,
while the old proxy files are going to be ignored.
