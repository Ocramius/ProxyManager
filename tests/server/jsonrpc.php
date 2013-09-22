<?php

require __DIR__ . '/../../vendor/autoload.php';

interface FooService
{
    public function foo();
}

interface BazService
{
    public function baz($param);
}

class Foo implements FooService, BazService
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
$server->setClass('Foo', 'FooService');  // my FooService implementation
$server->setClass('Foo', 'BazService');  // my BazService implementation
$server->handle();