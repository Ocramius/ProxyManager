<?php

require __DIR__ . '/../../vendor/autoload.php';

interface FooServiceInterface
{
    public function foo();
}

interface BazServiceInterface
{
    public function baz($param);
}

class Foo implements FooServiceInterface, BazServiceInterface
{
    public function foo()
    {
        return 'bar remote';
    }
    
    public function baz($param)
    {
        return $param . ' remote';
    }
}

$server = new Zend\Json\Server\Server();
$server->setClass('Foo', 'FooServiceInterface');  // my FooServiceInterface implementation
$server->setClass('Foo', 'BazServiceInterface');  // my BazServiceInterface implementation
$server->handle();