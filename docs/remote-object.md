# Remote Object Proxy

The remote object implementation is a mechanism that enables an local object to control an other object on an other server.
Each call method on the local object will do a network call to get remote object information.

## What is remote object proxy ?

A remote object is building on an interface. The distant interface defines the methods which a customer can call. This interface
will be use like a service name and, customer and remote service must implement it. Once the factory built, you have to
choose an adapter to create the remote proxy.

## Usage examples

Here your remote XmlRpc server :

```php
interface FooService
{
    public function foo();
}

class Foo implements FooService, BazService
{
    /**
     * Foo function
     * @return string
     */
    public function foo()
    {
        return 'bar remote';
    }
}

$server = new Zend\XmlRpc\Server();
$server->setClass('Foo', 'FooService');  // my FooService implementation
$server->handle();
```

And here your proxy :

```php
interface FooService
{
    public function foo();
}

$factory = new \ProxyManager\Factory\RemoteObjectFactory($configuration);
$adapter = new \ProxyManager\Factory\RemoteObject\Adapter\XmlRpc(
    'http://127.0.0.1/xmlrpc.php' // your XmlRpc host
);

$proxy = $factory->createProxy('FooService', $adapter);

var_dump($proxy->foo()); // "bar remote"
```

Three adapters are available by default : XmlRpc, JsonRpc & Soap. Custom adapter must implement \ProxyManager\Factory\RemoteObject\AdapterInterface :

```php
interface AdapterInterface
{
    /**
     * Call remote object
     *
     * @param string $wrappedClass
     * @param string $method
     * @param array $params
     */
    public function call($wrappedClass, $method, array $params = array());
}
```

It is very easy to create your own implementation (Rest for example) !

## Tuning performance for production

See [Tuning ProxyManager for Production](https://github.com/Ocramius/ProxyManager/blob/master/docs/tuning-for-production.md).
