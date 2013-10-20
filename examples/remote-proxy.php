<?php

require_once __DIR__ . '/../vendor/autoload.php';

use ProxyManager\Factory\RemoteObject\Adapter\XmlRpc;
use ProxyManager\Factory\RemoteObjectFactory;
use Zend\XmlRpc\Client;

if (! class_exists('Zend\XmlRpc\Client')) {
    echo "This example needs Zend\\XmlRpc\\Client to run. \n In order to install it, "
    . "please run following:\n\n"
    . "\$ php composer.phar require zendframework/zend-xmlrpc:2.*\n\n";

    exit(2);
}

class Foo
{
    public function bar()
    {
        return 'bar local!';
    }
}

$factory = new RemoteObjectFactory(
    new XmlRpc(new Client('http://localhost:9876/remote-proxy/remote-proxy-server.php'))
);
$proxy = $factory->createProxy('Foo');

try {
    var_dump($proxy->bar()); // bar remote !
} catch (\Zend\Http\Client\Adapter\Exception\RuntimeException $error) {
    echo "To run this example, please following before:\n\n\$ php -S localhost:9876 -t \"" . __DIR__ . "\"\n";

    exit(2);
}
