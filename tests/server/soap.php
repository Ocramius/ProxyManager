<?php

require_once __DIR__ . '/../Bootstrap.php';

$server = new Zend\Soap\Server(__DIR__ . '/soap.wsdl');

$server->setClass('ProxyManagerTestAsset\RemoteProxy\Foo');

$server->handle();
