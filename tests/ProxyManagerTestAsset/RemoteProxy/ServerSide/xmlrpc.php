<?php

$server = new Zend\XmlRpc\Server();

$server->setClass('ProxyManagerTestAsset\RemoteProxy\Foo', 'ProxyManagerTestAsset\RemoteProxy\FooServiceInterface');
$server->setClass('ProxyManagerTestAsset\RemoteProxy\Foo', 'ProxyManagerTestAsset\RemoteProxy\BazServiceInterface');

$server->setRequest($request); // request variable is auto included for tests
$server->handle();
