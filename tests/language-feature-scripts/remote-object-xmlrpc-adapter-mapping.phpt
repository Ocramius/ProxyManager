--TEST--
Verifies that generated remote object can call public method
--FILE--
<?php

require_once __DIR__ . '/init.php';

interface Kitchen
{
    public function foo();
}

$factory = new \ProxyManager\Factory\RemoteObjectFactory($configuration);
$adapter = new \ProxyManager\Factory\RemoteObject\Adapter\XmlRpc(array(
    'host' => 'http://127.0.0.1/xmlrpc.php?mapping', // host to /tests/server/xmlrpc.php
), array('Kitchen' => 'KitchenService'));

$proxy = $factory->createProxy('Kitchen', $adapter);

var_dump($proxy->foo());
?>
--EXPECT--
string(10) "bar remote"
