<?php

require __DIR__ . '/../../vendor/autoload.php';

class Kitchen
{
    public function foo()
    {
        return 'bar remote';
    }
}

$server = new Zend\Json\Server\Server();
$kitchen = isset($_GET['mapping']) ? 'KitchenService' : 'Kitchen';
$server->setClass('Kitchen', $kitchen);
$server->handle();