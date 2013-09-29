<?php

require __DIR__ . '/../../vendor/autoload.php';

interface FooServiceInterface
{
    public function foo();
}

class Foo implements FooServiceInterface
{
    public function foo()
    {
        return 'bar remote';
    }
}

$server = new Zend\Soap\Server('soap.wsdl');
$server->setClass('Foo'); // my FooServiceInterface implementation
$server->handle();
