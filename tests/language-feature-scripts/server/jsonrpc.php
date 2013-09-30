<?php

$server = new Zend\Json\Server\Server();
$server->setClass('ProxyManagerTestAsset\RemoteProxy\Foo', 'ProxyManagerTestAsset\RemoteProxy\FooServiceInterface');
$server->setClass('ProxyManagerTestAsset\RemoteProxy\Foo', 'ProxyManagerTestAsset\RemoteProxy\BazServiceInterface');

$callback = new Zend\Server\Method\Callback();

$callback
    ->setType('instance')
    ->setClass('ProxyManagerTestAsset\RemoteProxy\Foo')
    ->setMethod('__get');

$server->loadFunctions(
    array(
        new Zend\Server\Method\Definition(
            array(
                'name' => 'ProxyManagerTestAsset\RemoteProxy\FooServiceInterface.__get',
                'callback' => $callback,
            )
        )
    )
);

$server->setRequest($request); // request variable is auto included for tests
$server->handle();
