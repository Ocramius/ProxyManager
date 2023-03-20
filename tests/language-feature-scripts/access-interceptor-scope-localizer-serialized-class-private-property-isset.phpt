--TEST--
Verifies that generated access interceptors doesn't throw PHP Warning on Serialized class private property direct isset check
--FILE--
<?php
error_reporting(E_ALL & ~E_DEPRECATED);

require_once __DIR__ . '/init.php';

class Kitchen implements \Serializable
{
    private $sweets = 'candy';

    function serialize()
    {
        return $this->sweets;
    }

    function unserialize($serialized)
    {
        $this->sweets = $serialized;
    }
}

$factory = new \ProxyManager\Factory\AccessInterceptorScopeLocalizerFactory($configuration);

$proxy = $factory->createProxy(new Kitchen());

var_dump(isset($proxy->sweets));
?>
--EXPECT--
bool(false)
