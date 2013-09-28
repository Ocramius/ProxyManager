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
$server->setClass('Foo', 'FooServiceInterface');  // my FooServiceInterface implementation
$server->setClass('Foo', 'BazServiceInterface');  // my BazServiceInterface implementation
$server->handle();