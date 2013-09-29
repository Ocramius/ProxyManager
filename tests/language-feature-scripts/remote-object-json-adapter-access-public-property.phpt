--TEST--
Verifies that generated remote object can call public property
--FILE--
<?php

require_once __DIR__ . '/init.php';

interface FooServiceInterface
{
    public function foo();
}

class Foo implements FooServiceInterface
{
    public $foo = "baz";
    
    public function foo()
    {
        return 'bar';
    }
}

$factory = new \ProxyManager\Factory\RemoteObjectFactory($configuration);
$adapter = new \ProxyManager\Factory\RemoteObject\Adapter\JsonRpc(
    'http://127.0.0.1/jsonrpc.php' // host to /tests/server/jsonrpc.php
);

$proxy = $factory->createProxy('FooServiceInterface', $adapter);

var_dump($proxy->foo);
?>
--EXPECT--
string(10) "foo remote"
