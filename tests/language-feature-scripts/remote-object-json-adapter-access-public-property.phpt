--TEST--
Verifies that generated remote object can call public property
--FILE--
<?php

require_once __DIR__ . '/init.php';

use ProxyManagerTestAsset\RemoteProxy\Client\LocalHttp;

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
    'http://127.0.0.1:8080/jsonrpc.php' // host to /tests/server/jsonrpc.php
);

/**
 * Only for local tests
 * Don't include this line in your code
 */
$adapter->getClient()->setHttpClient(new LocalHttp(__DIR__ . '/server/jsonrpc.php', 'json-rpc')); 

$proxy = $factory->createProxy('ProxyManagerTestAsset\RemoteProxy\FooServiceInterface', $adapter);

var_dump($proxy->foo);
?>
--EXPECT--
string(10) "foo remote"
