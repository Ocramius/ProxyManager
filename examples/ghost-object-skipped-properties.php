<?php

declare(strict_types=1);

namespace ProxyManager\Example\GhostObjectSkippedProperties;

use Closure;
use ProxyManager\Factory\LazyLoadingGhostFactory;
use ProxyManager\Proxy\GhostObjectInterface;
use ReflectionProperty;

require_once __DIR__ . '/../vendor/autoload.php';

class User
{
    private ?int $id = null;

    private ?string $username = null;

    public function getId() : ?int
    {
        return $this->id;
    }

    public function getUsername() : ?string
    {
        return $this->username;
    }
}

(static function () : void {
    $proxy = (new LazyLoadingGhostFactory())->createProxy(
        User::class,
        static function (
            GhostObjectInterface $proxy,
            string $method,
            array $parameters,
            ?Closure & $initializer,
            array $properties
        ) {
            $initializer = null;

            var_dump('Triggered lazy-loading!');

            $properties["\0ProxyManager\\Example\\GhostObjectSkippedProperties\\User\0username"] = 'Ocramius';

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
})();
