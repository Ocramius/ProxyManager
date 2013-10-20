<?php

use Zend\XmlRpc\Server;

require_once __DIR__ . '/../../vendor/autoload.php';

class Foo
{
    public function bar()
    {
        return 'bar remote!';
    }
}

$server = new Server();

$server->setClass(new Foo(), 'Foo');
$server->setReturnResponse(false);

$server->handle();
