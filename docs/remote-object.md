# Remote Object Proxy

The remote object implementation is a mechanism that enables an local object to control an other object on an other server.
Each call method on the local object will do a network call to get information or execute operations on the remote object.

## What is remote object proxy ?

A remote object is based on an interface. The remote interface defines the API that a consumer can call. This interface 
must be implemented both by the client and the RPC server.

## Usage examples

RPC server side code :

```php
interface FooServiceInterface
{
    public function foo();
}

class Foo implements FooServiceInterface
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
$server->setClass('Foo', 'FooServiceInterface');  // my FooServiceInterface implementation
$server->handle();
```

Client side code (proxy) :

```php
interface FooServiceInterface
{
    public function foo();
}

$factory = new \ProxyManager\Factory\RemoteObjectFactory($configuration);
$adapter = new \ProxyManager\Factory\RemoteObject\Adapter\XmlRpc(
    'http://127.0.0.1/xmlrpc.php'
);

$proxy = $factory->createProxy('FooServiceInterface', $adapter);

var_dump($proxy->foo()); // "bar remote"
```

Three adapters are available by default : `ProxyManager\Factory\RemoteObject\Adapter\XmlRpc`, `ProxyManager\Factory\RemoteObject\Adapter\JsonRpc` & `ProxyManager\Factory\RemoteObject\Adapter\Soap`. Custom adapter must implement `ProxyManager\Factory\RemoteObject\AdapterInterface` :

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
