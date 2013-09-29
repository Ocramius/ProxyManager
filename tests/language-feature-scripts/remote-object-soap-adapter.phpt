--TEST--
Verifies that generated remote object can call public method
--FILE--
<?php

require_once __DIR__ . '/init.php';

interface FooServiceInterface
{
    public function foo();
}

$factory = new \ProxyManager\Factory\RemoteObjectFactory($configuration);
$adapter = new \ProxyManager\Factory\RemoteObject\Adapter\Soap(
    'http://127.0.0.1:8080/soap.wsdl' // host to /tests/server/soap.php
);
$adapter->getClient()->setSoapVersion(SOAP_1_1);

$proxy = $factory->createProxy('ProxyManagerTestAsset\RemoteProxy\FooServiceInterface', $adapter);

var_dump($proxy->foo());
?>
--EXPECT--
string(10) "bar remote"
