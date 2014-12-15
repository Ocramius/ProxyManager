--TEST--
Verifies that generated lazy loading ghost objects allows checking property `isset()` in private class scope
--FILE--
<?php

require_once __DIR__ . '/init.php';

class Kitchen
{
    private $sweet = 'yummy!';
    private $sour  = null;

    public function hasSweet()
    {
        return isset($this->sweet);
    }

    public function hasSour()
    {
        return isset($this->sour);
    }
}

$factory = new \ProxyManager\Factory\LazyLoadingGhostFactory($configuration);

var_dump($factory->createProxy(Kitchen::class, function () {})->hasSweet());
var_dump($factory->createProxy(Kitchen::class, function () {})->hasSour());

?>
--EXPECTF--
bool(true)
bool(false)