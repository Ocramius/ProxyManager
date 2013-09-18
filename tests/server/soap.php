<?php

require __DIR__ . '/../../vendor/autoload.php';

class Kitchen
{
    public function foo()
    {
        return 'bar remote';
    }
}

$server = new Zend\Soap\Server(__DIR__ . '/soap.wsdl');
$server->setClass('Kitchen');
$server->handle();