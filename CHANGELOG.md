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

### PHP 5.6 Variadics support

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
