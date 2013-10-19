<?php

class FooServerSide
{
    public function bar()
    {
        return 'bar remote !';
    }
}

$alias = array('FooClientSide' => 'FooServerSide');

// extract infos from service name called
list($className, $methodName) = explode('.', $serviceName);

if (isset($alias[$className])) {
    $className = $alias[$className];
}

$result = call_user_func_array(array(new $className, $methodName), $params);
