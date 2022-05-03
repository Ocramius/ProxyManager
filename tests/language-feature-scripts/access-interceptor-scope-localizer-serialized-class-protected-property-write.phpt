--TEST--
Verifies that generated access interceptors doesn't throw PHP Warning on Serialized class protected property direct write
--FILE--
<?php

require_once __DIR__ . '/init.php';

class Kitchen implements \Serializable
{
    protected $sweets = 'candy';

    function serialize()
    {
        return $this->sweets;
    }

    function unserialize($serialized)
    {
        $this->sweets = $serialized;
    }

    function __serialize()
    {
        return $this->serialize();
    }

    function __unserialize($serialized): void
    {
        $this->unserialize($serialized);
    }
}

$factory = new \ProxyManager\Factory\AccessInterceptorScopeLocalizerFactory($configuration);

$proxy = $factory->createProxy(new Kitchen());

$proxy->sweets = 'stolen';
?>
--EXPECTF--
%SFatal error:%sCannot %s property%sin %a
