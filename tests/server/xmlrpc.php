<?php

require_once __DIR__ . '/../Bootstrap.php';

$server = new Zend\XmlRpc\Server();

$server->setClass('ProxyManagerTestAsset\RemoteProxy\Foo', 'ProxyManagerTestAsset\RemoteProxy\FooServiceInterface');
$server->setClass('ProxyManagerTestAsset\RemoteProxy\Foo', 'ProxyManagerTestAsset\RemoteProxy\BazServiceInterface');

$server->handle();
