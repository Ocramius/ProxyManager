<?php

require __DIR__ . '/../../vendor/autoload.php';

interface FooService
{
    public function foo();
}

class Foo implements FooService
{
    public function foo()
    {
        return 'bar remote';
    }
}

$server = new Zend\XmlRpc\Server();
$server->setClass('Foo', 'FooService');  // my FooService implementation
$server->handle();