<?php

namespace StaticAnalysis\AccessInterceptorValueHolder;

use ProxyManager\Factory\AccessInterceptorValueHolderFactory;

require_once __DIR__ . '/../../vendor/autoload.php';

class MyProxiedClass
{
    public function sayHello() : string
    {
        return 'Hello!';
    }
}

(static function () : void {
    echo (new AccessInterceptorValueHolderFactory())
        ->createProxy(
            new MyProxiedClass(),
            [
                'sayHello' => static function (
                    object $proxy,
                    MyProxiedClass $realInstance,
                    string $method,
                    array $parameters,
                    bool & $returnEarly
                ) {
                    echo 'pre-';
                },
            ],
            [
                'sayHello' =>
                /** @param mixed $returnValue */
                    static function (
                        object $proxy,
                        MyProxiedClass $realInstance,
                        string $method,
                        array $parameters,
                        & $returnValue,
                        bool & $overrideReturnValue
                    ) {
                        echo 'post-';
                    },
            ]
        )
        ->sayHello();

    $valueHolderInterceptor = (new AccessInterceptorValueHolderFactory())
        ->createProxy(new MyProxiedClass());

    $valueHolderInterceptor->setMethodPrefixInterceptor(
        'sayHello',
        static function (
            object $proxy,
            MyProxiedClass $realInstance,
            string $method,
            array $parameters,
            bool & $returnEarly
        ) {
            echo 'pre-';
        }
    );

    $valueHolderInterceptor->setMethodSuffixInterceptor(
        'sayHello',
        /** @param mixed $returnValue */
        static function (
            object $proxy,
            MyProxiedClass $realInstance,
            string $method,
            array $parameters,
            & $returnValue,
            bool & $returnEarly
        ) {
            echo 'post-';
        }
    );

    echo $valueHolderInterceptor->sayHello();

    $interceptedValue = $valueHolderInterceptor
        ->getWrappedValueHolderValue();

    assert($interceptedValue !== null);

    echo $interceptedValue->sayHello();
})();
