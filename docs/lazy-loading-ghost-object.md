# Lazy Loading Ghost Object Proxies

A lazy loading ghost object proxy is a ghost proxy that looks exactly like the real instance of the proxied subject,
but which has all properties nulled before initialization.

## Lazy loading with the Ghost Object

In pseudo-code, in userland, [lazy loading](http://www.martinfowler.com/eaaCatalog/lazyLoad.html) in a ghost object
looks like following:

```php
class MyObjectProxy
{
    private $initialized = false;
    private $name;
    private $surname;

    public function doFoo()
    {
        $this->init();

        // Perform doFoo routine using loaded variables
    }

    private function init()
    {
        if (! $this->initialized) {
            $data          = some_logic_that_loads_data();

            $this->name    = $data['name'];
            $this->surname = $data['surname'];

            $this->initialized = true;
        }
    }
}
```

Ghost objects work similarly to virtual proxies, but since they don't wrap around a "real" instance of the proxied
subject, they are better suited for representing dataset rows.

## When do I use a ghost object?

You usually need a ghost object in cases where following applies

 * you are building a small data-mapper and want to lazily load data across associations in your object graph
 * you want to initialize objects representing rows in a large dataset
 * you want to compare instances of lazily initialized objects without the risk of comparing a proxy with a real subject
 * you are aware of the internal state of the object and are confident in working with its internals via reflection
   or direct property access

## Usage examples

[ProxyManager](https://github.com/Ocramius/ProxyManager) provides a factory that creates lazy loading ghost objects.
To use it, follow these steps:

First of all, define your object's logic without taking care of lazy loading:

```php
namespace MyApp;

class Customer
{
    private $name;
    private $surname;

    // just write your business logic or generally logic
    // don't worry about how complex this object will be!
    // don't code lazy-loading oriented optimizations in here!
    public function getName() { return $this->name; }
    public function setName($name) { $this->name = (string) $name; }
    public function getSurname() { return $this->surname; }
    public function setSurname($surname) { $this->surname = (string) $surname; }
}
```

Then use the proxy manager to create a ghost object of it.
You will be responsible of setting its state during lazy loading:

```php
namespace MyApp;

use ProxyManager\Factory\LazyLoadingGhostFactory;
use ProxyManager\Proxy\LazyLoadingInterface;

require_once __DIR__ . '/vendor/autoload.php';

$factory     = new LazyLoadingGhostFactory();
$initializer = function (LazyLoadingInterface $proxy, $method, array $parameters, & $initializer) {
    $initializer   = null; // disable initialization

    // load data and modify the object here
    $proxy->setName('Agent');
    $proxy->setSurname('Smith');

    return true; // confirm that initialization occurred correctly
};

$instance = $factory->createProxy('MyApp\Customer', $initializer);
```

You can now simply use your object as before:

```php
// this will just work as before
echo $proxy->getName() . ' ' . $proxy->getSurname(); // Agent Smith
```

## Lazy Initialization

As you can see, we use a closure to handle lazy initialization of the proxy instance at runtime.
The initializer closure signature for ghost objects should be as following:

```php
/**
 * @var object  $proxy         the instance the ghost object proxy that is being initialized
 * @var string  $method        the name of the method that triggered lazy initialization
 * @var array   $parameters    an ordered list of parameters passed to the method that
 *                             triggered initialization, indexed by parameter name
 * @var Closure $initializer   a reference to the property that is the initializer for the
 *                             proxy. Set it to null to disable further initialization
 *
 * @return bool true on success
 */
$initializer = function ($proxy, $method, $parameters, & $initializer) {};
```

The initializer closure should usually be coded like following:

```php
$initializer = function ($proxy, $method, $parameters, & $initializer) {
    $initializer = null; // disable initializer for this proxy instance

    // modify the object with loaded data
    $proxy->setFoo(/* ... */);
    $proxy->setBar(/* ... */);

    return true; // report success
};
```

The
[`ProxyManager\Factory\LazyLoadingGhostFactory`](https://github.com/Ocramius/ProxyManager/blob/master/src/ProxyManager/Factory/LazyLoadingGhostFactory.php)
produces proxies that implement both the
[`ProxyManager\Proxy\GhostObjectInterface`](https://github.com/Ocramius/ProxyManager/blob/master/src/ProxyManager/Proxy/GhostObjectInterface.php)
and the
[`ProxyManager\Proxy\LazyLoadingInterface`](https://github.com/Ocramius/ProxyManager/blob/master/src/ProxyManager/Proxy/LazyLoadingInterface.php).

At any point in time, you can set a new initializer for the proxy:

```php
$proxy->setProxyInitializer($initializer);
```

In your initializer, you **MUST** turn off any further initialization:

```php
$proxy->setProxyInitializer(null);
```

or

```php
$initializer = null; // if you use the initializer passed by reference to the closure
```

## Triggering Initialization

A lazy loading ghost object is initialized whenever you access any property or method of it.
Any of the following interactions would trigger lazy initialization:

```php
// calling a method
$proxy->someMethod();

// reading a property
echo $proxy->someProperty;

// writing a property
$proxy->someProperty = 'foo';

// checking for existence of a property
isset($proxy->someProperty);

// removing a property
unset($proxy->someProperty);

// cloning the entire proxy
clone $proxy;

// serializing the proxy
$unserialized = unserialize(serialize($proxy));
```

Remember to call `$proxy->setProxyInitializer(null);` to disable initialization of your proxy, or it will happen more
than once.

## Proxying interfaces

You can also generate proxies from an interface FQCN. By proxying an interface, you will only be able to access the
methods defined by the interface itself, even if the `wrappedObject` implements more methods. This will anyway save
some memory since the proxy won't contain any properties.

## Tuning performance for production

See [Tuning ProxyManager for Production](https://github.com/Ocramius/ProxyManager/blob/master/docs/tuning-for-production.md).
