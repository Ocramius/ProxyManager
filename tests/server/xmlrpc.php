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
    /**
     * Foo function
     * @return string
     */
    public function foo()
    {
        return 'bar remote';
    }
    
    /**
     * Baz function
     * @param string $param
     * @return string
     */
    public function baz($param)
    {
        return $param . ' remote';
    }
}

$server = new Zend\XmlRpc\Server();
$server->setClass('Foo', 'FooService');  // my FooService implementation
$server->setClass('Foo', 'BazService');  // my BazService implementation
$server->handle();