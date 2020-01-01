<?php

declare(strict_types=1);

namespace ProxyManager\Example\RemoteProxyServer;

use Laminas\XmlRpc\Server;

require_once __DIR__ . '/../../vendor/autoload.php';

class Foo
{
    public function bar() : string
    {
        return 'bar remote!';
    }
}

(static function () : void {
    $server = new Server();

    $server->setClass(new Foo(), 'Foo');
    $server->setReturnResponse(false);

    $server->handle();
})();
