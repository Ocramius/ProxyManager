--TEST--
Verifies that generated access interceptors disallow protected property direct write
--FILE--
<?php

require_once __DIR__ . '/init.php';

class Kitchen
{
    public $first;
    public $second;

    public function __construct($first, ...$second)
    {
        $this->first  = $first;
        $this->second = $second;
    }
}

$factory = new \ProxyManager\Factory\AccessInterceptorScopeLocalizerFactory($configuration);

$proxy = $factory->createProxy(new Kitchen('First One', 'Marco Pivetta', 'Jefersson Nathan', 'Danizord'));

var_dump($proxy->first);
var_dump($proxy->second);
?>
--EXPECTF--
string(9) "First One"
array(3) {
  [0]=>
  string(13) "Marco Pivetta"
  [1]=>
  string(16) "Jefersson Nathan"
  [2]=>
  string(8) "Danizord"
}
