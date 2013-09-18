--TEST--
Verifies that generated remote object can call public method
--FILE--
<?php

require_once __DIR__ . '/init.php';

interface FooService
{
    public function foo();
}

$factory = new \ProxyManager\Factory\RemoteObjectFactory($configuration);
$adapter = new \ProxyManager\Factory\RemoteObject\Adapter\Soap(
    'http://127.0.0.1/soap.wsdl?t'.time() // host to /tests/server/soap.php
);

$proxy = $factory->createProxy('FooService', $adapter);

var_dump($proxy->foo());
?>
--EXPECT--
string(10) "bar remote"
