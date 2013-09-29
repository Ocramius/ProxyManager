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
    
    public function __get($name)
    {
        return $name . ' remote';
    }
}

$server = new Zend\Json\Server\Server();
$server->setClass('Foo', 'FooServiceInterface');  // my FooServiceInterface implementation
$server->setClass('Foo', 'BazServiceInterface');  // my BazServiceInterface implementation

$callback = new Zend\Server\Method\Callback();
$callback->setType('instance')
            ->setClass('Foo')
            ->setMethod('__get');

$server->loadFunctions(
    array(
        new Zend\Server\Method\Definition(
            array(
                'name' => 'FooServiceInterface.__get',
                'invokeArguments' => array('name'),
                'callback' => $callback,
            )
        )
    )
);
$server->handle();
