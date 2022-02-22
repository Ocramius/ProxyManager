--TEST--
Verifies that generated value holder proxy object doesn't loose track of references inside its interceptors
--FILE--
<?php

require_once __DIR__ . '/init.php';

class RefClass
{
    public function ref1(int &$ref): void
    {
        ++$ref;
    }

    public function ref2(int &$ref): void
    {
        --$ref;
    }
}

$factory = new \ProxyManager\Factory\AccessInterceptorValueHolderFactory($configuration);
$proxy = $factory->createProxy(
    new RefClass(),
    [
        'ref1' => static function(
            object $proxy,
            RefClass $instance,
            string $method,
            array $args,
            bool &$returnEarly
        ) {
            $returnEarly = true;
            $instance->ref1($args['ref']);
        },
    ],
    [
        'ref2' => static function(
            object $proxy,
            RefClass $instance,
            string $method,
            array $args,
        ) {
            $instance->ref2($args['ref']);
        },
    ],
);

$ref = 0;

$proxy->ref1($ref);
var_dump($ref);

$proxy->ref2($ref);
var_dump($ref);

--EXPECTF--
int(1)
int(-1)
