<?php

declare(strict_types=1);

namespace ProxyManager\Example\RemoteProxy;

use ProxyManager\Factory\RemoteObject\Adapter\XmlRpc;
use ProxyManager\Factory\RemoteObjectFactory;
use Laminas\Http\Client\Adapter\Exception\RuntimeException;
use Laminas\XmlRpc\Client;

require_once __DIR__ . '/../vendor/autoload.php';

if (! class_exists('Laminas\XmlRpc\Client')) {
    echo "This example needs Laminas\\XmlRpc\\Client to run. \n In order to install it, "
        . "please run following:\n\n"
        . "\$ php composer.phar require laminas/laminas-xmlrpc:2.*\n\n";

    exit(2);
}

class Foo
{
    public function bar() : string
    {
        return 'bar local!';
    }
}

(static function () : void {
    $factory = new RemoteObjectFactory(
        new XmlRpc(new Client('http://localhost:9876/remote-proxy/remote-proxy-server.php'))
    );
    $proxy   = $factory->createProxy(Foo::class);

    try {
        var_dump($proxy->bar()); // bar remote !
    } catch (RuntimeException $error) {
        echo "To run this example, please following before:\n\n\$ php -S localhost:9876 -t \"" . __DIR__ . "\"\n";

        exit(2);
    }
})();
