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

$server = new Zend\Soap\Server('soap.wsdl');
$server->setClass('Foo'); // my FooService implementation
$server->handle();