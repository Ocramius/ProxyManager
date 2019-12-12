--TEST--
Verifies that generated access interceptors doesn't throw PHP Warning on Serialized class private property direct read
--FILE--
<?php

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

$proxy->sweets;
?>
--EXPECTF--
%SFatal error:%sCannot access private property %s::$sweets in %a
