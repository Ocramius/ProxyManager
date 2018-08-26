--TEST--
Verifies that generated remote object can call public property
--FILE--
<?php

require_once __DIR__ . '/init.php';

use ProxyManager\Factory\RemoteObject\AdapterInterface;

interface FooServiceInterface
{
    public function fooBar();
}

class Foo implements FooServiceInterface
{
    public $foo = 'baz';
    
    public function fooBar()
    {
        return 'bar';
    }
}

class CustomAdapter implements AdapterInterface
{
    public function call(string $wrappedClass, string $method, array $params = [])
    {
        return 'baz';
    }
}

$factory = new \ProxyManager\Factory\RemoteObjectFactory(new CustomAdapter(), $configuration);
/** @var FooServiceInterface $proxy */
$proxy   = $factory->createProxy(FooServiceInterface::class);

echo $proxy->fooBar() . "\n";
$proxy->unknown();
?>
--EXPECTF--
baz

%AFatal error:%sCall to undefined method %s::unknown%S in %A
