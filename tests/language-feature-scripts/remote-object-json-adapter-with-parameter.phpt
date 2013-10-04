--TEST--
Verifies that generated remote object can call public method
--FILE--
<?php

require_once __DIR__ . '/init.php';

use ProxyManagerTestAsset\RemoteProxy\Client\LocalHttp;
use Zend\Json\Server\Client;

interface FooServiceInterface
{
    public function foo();
}

interface BazServiceInterface
{
    public function baz($param);
}

$factory = new \ProxyManager\Factory\RemoteObjectFactory($configuration);
$adapter = new \ProxyManager\Factory\RemoteObject\Adapter\JsonRpc(
    new Client('http://127.0.0.1:8080/jsonrpc.php') // host to /tests/server/jsonrpc.php
);

/**
 * Only for local tests
 * Don't include this line in your code
 */
$adapter->getClient()->setHttpClient(new LocalHttp(__DIR__ . '/server/jsonrpc.php', 'json-rpc')); 

$proxy = $factory->createProxy('ProxyManagerTestAsset\RemoteProxy\BazServiceInterface', $adapter);

var_dump($proxy->baz('baz'));
?>
--EXPECT--
string(10) "baz remote"
