<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use ProxyManager\Factory\LazyLoadingGhostFactory;
use ProxyManager\Proxy\GhostObjectInterface;

class User
{
    private $id;
    private $username;

    public function getId() : int
    {
        return $this->id;
    }

    public function getUsername() : string
    {
        return $this->username;
    }
}

/** @var User $proxy */
$proxy = (new LazyLoadingGhostFactory())->createProxy(
    User::class,
    function (GhostObjectInterface $proxy, string $method, array $parameters, & $initializer, array $properties) {
        $initializer = null;

        var_dump('Triggered lazy-loading!');

        $properties["\0User\0username"] = 'Ocramius';

        return true;
    },
    [
        'skippedProperties' => ["\0User\0id"],
    ]
);

$idReflection = new ReflectionProperty(User::class, 'id');

$idReflection->setAccessible(true);
$idReflection->setValue($proxy, 123);

var_dump($proxy->getId());
var_dump($proxy->getUsername());
