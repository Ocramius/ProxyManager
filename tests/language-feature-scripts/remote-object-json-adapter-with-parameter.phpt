--TEST--
Verifies that generated remote object can call public method
--FILE--
<?php

require_once __DIR__ . '/init.php';

interface FooService
{
    public function foo();
}

interface BazService
{
    public function baz($param);
}

$factory = new \ProxyManager\Factory\RemoteObjectFactory($configuration);
$adapter = new \ProxyManager\Factory\RemoteObject\Adapter\JsonRpc(
    'http://127.0.0.1/jsonrpc.php' // host to /tests/server/jsonrpc.php
);

$proxy = $factory->createProxy('BazService', $adapter);

var_dump($proxy->baz('baz'));
?>
--EXPECT--
string(10) "baz remote"
