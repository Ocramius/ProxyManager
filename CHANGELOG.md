---
title: Changelog
---

This is a list of changes/improvements that were introduced in ProxyManager

## 2.0.0


### New features

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

Every factory in the `ProxyManager\Factory` namespace is now capable of dealing with
this type of API.
