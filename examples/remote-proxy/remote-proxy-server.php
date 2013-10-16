<?php

class FooServerSide
{
    public function bar()
    {
        return 'bar remote !';
    }
}

$alias = array(
    'FooClientSide' => 'FooServerSide',
);

// extract infos from service name called
$infos = explode('.', $serviceName);
$className = $infos[0];
if (isset($alias[$className])) {
    $className = $alias[$className];
}
$class = new $className;
$result = call_user_func_array(array($class, $infos[1]), $params);