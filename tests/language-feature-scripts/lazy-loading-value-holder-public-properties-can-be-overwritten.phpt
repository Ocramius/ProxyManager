--TEST--
Verifies that lazy loading value holder proxy file is generated
--FILE--
<?php

use ProxyManager\Factory\LazyLoadingValueHolderFactory;

require_once __DIR__ . '/init.php';

class PublicPropertyTest
{
    /** @var string */
    public $variable;

    public function test()
    {
        echo "\nin test function";
    }
}

$proxyFactory = new LazyLoadingValueHolderFactory();

$test = $proxyFactory->createProxy(
    PublicPropertyTest::class,
    function (&$service, $proxy, $method, $parameters, &$initializer) {
        $initializer = null;
        $service = new PublicPropertyTest();
        $service->variable = 'set while initialized';

        return true;
    }
);
echo "\n" . $test->variable;
$test->variable = 'changed 1';
echo "\n" . $test->variable;
$test->test();
echo "\n" . $test->variable;
$test->variable = 'changed 2';
echo "\n" . $test->variable;

?>
--EXPECT--
set while initialized
changed 1
in test function
changed 1
changed 2
