--TEST--
Verifies that generated remote object will pass default parameters in method declaration to Adapter.
--FILE--
<?php

require_once __DIR__ . '/init.php';

use ProxyManager\Factory\RemoteObject\AdapterInterface;

interface FooServiceInterface
{
    public function fooBar(string $requiredParam, string $optionalParam = 'default', string ...$strs);
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
echo $proxy->fooBar('required', 'passed') . "\n";
echo $proxy->fooBar('required', 'passed', 'first_variadic') . "\n";
echo $proxy->fooBar('required', 'passed', 'first_variadic', 'second_variadic') . "\n";
?>
--EXPECTF--
required,default
required,passed
required,passed,first_variadic
required,passed,first_variadic,second_variadic
