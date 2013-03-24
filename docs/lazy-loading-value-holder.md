# Lazy Loading Value Holder Proxy

A lazy loading value holder proxy is an object that is wrapping a lazily initialized "real" instance of the proxied
class. In pseudo-code, it looks like following:

```php
class MyObjectProxy
{
    private $wrapped;

    public function doFoo()
    {
        if (null === $this->wrapped) {
            $this->wrapped = new MyObject();
        }

        return $this->wrapped->doFoo();
    }
}
```

## When do I use a lazy value holder?

You usually need a lazy value holder in cases where following applies

 * your object takes a lot of time and memory to be initialized (with all dependencies)
 * your object is not always used, and the instantiation overhead can be avoided

## Instantiation

[ProxyManager](https://github.com/Ocramius/ProxyManager) provides a factory that eases instantiation of lazy loading
value holders:

```php
namespace MyApp;

use ProxyManager\Configuration;
use ProxyManager\Factory\LazyLoadingValueHolderFactory;
use ProxyManager\Proxy\LazyLoadingInterface;

require_once __DIR__ . '/vendor/autoload.php';

$config      = new Configuration();
$factory     = new LazyLoadingValueHolderFactory($config);
$initializer = function (LazyLoadingInterface $proxy, & $wrappedObject, $method, array $parameters) {
    // disable initialization
    $proxy->setProxyInitializer(null);

    // fill your object with values here
    $wrappedObject = new HeavyComplexObject();

    // confirm that initialization occurred correctly
    return true;
};

$instance    = $factory->createProxy('MyApp\HeavyComplexObject', $initializer);

$instance->doFoo();
```

As you can see, we use a closure to handle lazy initialization of the proxy instance at runtime. The parameters passed
to the initializer closure are following:

 1. `$proxy` - the instance proxy that is being initialized
 2. `& $wrappedObject` - the wrapped property that should be initialized with a correct object instance.
 3. `$method` - the method being called when lazy initialization has to occur
 4. `$parameters` - an ordered list of parameters, with keys being the parameter names, and values being their values.

_NOTE_: In future, a compliance validator class for such initializers may be provided.

## Initialization

A proxy is initialized whenever you access any property or method of it. Any of the following would trigger lazy
initialization:

```php
$proxy->someMethod();
echo $proxy->someProperty;
$proxy->someProperty = 'foo';
isset($proxy->someProperty);
unset($proxy->someProperty);
clone $proxy;
$unserialized = serialize(unserialize($proxy));
```

Remember to call `$proxy->setProxyInitializer(null);` to disable initialization of your proxy, or it will happen more
than once.

## Tuning performance for production

By default, `ProxyManager\Factory\LazyLoadingValueHolderFactory` generates a lazy loading proxy class and writes it to
disk at each request.

Proxy generation causes I/O operations and uses a lot of reflection, so be sure to have generated all of your proxies
before deploying your code on a live system, or you may experience poor performance.

You can configure ProxyManager so that it will try autoloading the proxies first.
Generating them "bulk" is not yet implemented:

```php
$config = new \ProxyManager\Configuration();

$config->setProxiesTargetDir(__DIR__ . '/my/generated/classes/cache/dir');
$config->setAutoGenerateProxies(false);

// then register the autoloader
spl_autoload_register($config->getProxyAutoloader());
```

Generating a classmap with all your proxy classes in it will also work.