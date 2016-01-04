--TEST--
Verifies that generated remote object can call public property
--FILE--
<?php

require_once __DIR__ . '/init.php';

use ProxyManager\Factory\RemoteObject\AdapterInterface;

interface FooServiceInterface
{
    public function fooBar();
}

class Foo implements FooServiceInterface
{
    public $foo = "baz";
    
    public function fooBar()
    {
        return 'bar';
    }
}

class CustomAdapter implements AdapterInterface
{
    public function call($wrappedClass, $method, array $params = [])
    {
        return 'baz';
    }
}

$factory = new \ProxyManager\Factory\RemoteObjectFactory(new CustomAdapter(), $configuration);
/* @var $proxy FooServiceInterface */
$proxy   = $factory->createProxy(FooServiceInterface::class);

var_dump($proxy->fooBar());
var_dump($proxy->unknown());
?>
--EXPECTF--
%Sstring(3) "baz"

%SFatal error:%sCall to undefined method %s::unknown%S in %s on line %d
