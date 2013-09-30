--TEST--
Verifies that generated remote object can call public method
--FILE--
<?php

require_once __DIR__ . '/init.php';

use ProxyManagerTestAsset\RemoteProxy\Client\LocalHttp;

interface FooServiceInterface
{
    public function foo();
}

interface BazServiceInterface
{
    /**
     * Baz function
     * @param string $param
     * @return string
     */
    public function baz($param);
}

$factory = new \ProxyManager\Factory\RemoteObjectFactory($configuration);
$adapter = new \ProxyManager\Factory\RemoteObject\Adapter\XmlRpc(
    'http://127.0.0.1:8080/xmlrpc.php' // host to /tests/server/xmlrpc.php
);

/**
 * Only for local tests
 * Don't include this line in your code
 */
$adapter->getClient()->setHttpClient(new LocalHttp(__DIR__ . '/server/xmlrpc.php', 'xml-rpc')); 

$proxy = $factory->createProxy('ProxyManagerTestAsset\RemoteProxy\BazServiceInterface', $adapter);

var_dump($proxy->baz('baz'));
?>
--EXPECT--
string(10) "baz remote"
