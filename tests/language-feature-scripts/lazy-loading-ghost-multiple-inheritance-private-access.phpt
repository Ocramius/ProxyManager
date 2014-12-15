--TEST--
Verifies that private API of proxies is guaranteed to access private properties in any case
--FILE--
<?php

require_once __DIR__ . '/init.php';

class Foo
{
    private $multiplier = 3;

    public function multiply($value)
    {
        return $this->multiplier * $value;
    }
}

class Bar extends Foo
{
    private $multiplier = 5;

    public function multiply($value)
    {
        return $value * parent::multiply($this->multiplier);
    }
}

class Baz extends Bar
{
    private $multiplier = 7;

    public function multiply($value)
    {
        return $value * parent::multiply($this->multiplier);
    }
}

echo (new \ProxyManager\Factory\LazyLoadingGhostFactory($configuration))
    ->createProxy(Baz::class, function () {})
    ->multiply(2);

?>
--EXPECTF--
210