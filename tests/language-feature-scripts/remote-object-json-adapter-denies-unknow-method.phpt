--TEST--
Verifies that generated remote object can call public property
--FILE--
<?php

require_once __DIR__ . '/init.php';

use ProxyManager\Factory\RemoteObject\AdapterInterface;
use Zend\Json\Server\Client;

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

class CustomAdapter implements AdapterInterface
{
    public function call($wrappedClass, $method, array $params = array())
    {
        return 'baz';
    }
}

$factory = new \ProxyManager\Factory\RemoteObjectFactory(new CustomAdapter(), $configuration);
$proxy   = $factory->createProxy('ProxyManagerTestAsset\RemoteProxy\FooServiceInterface');

var_dump($proxy->foo());
var_dump($proxy->unknown());
?>
--EXPECTF--
string(3) "baz"

%SFatal error: Call to undefined method %s::unknown%S in %s on line %d
