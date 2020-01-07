--TEST--
Verifies that generated remote object will pass default parameters in method declaration to Adapter.
--FILE--
<?php

require_once __DIR__ . '/init.php';

use ProxyManager\Factory\RemoteObject\AdapterInterface;

interface FooServiceInterface
{
    public function fooBar(string $requiredParam, string $optionalParam = 'default');
}

class CustomAdapter implements AdapterInterface
{
    public function call(string $wrappedClass, string $method, array $params = [])
    {
        return \implode(',', $params);
    }
}

$factory = new \ProxyManager\Factory\RemoteObjectFactory(new CustomAdapter(), $configuration);
/** @var FooServiceInterface $proxy */
$proxy   = $factory->createProxy(FooServiceInterface::class);

echo $proxy->fooBar('required') . "\n";
?>
--EXPECTF--
required,default
