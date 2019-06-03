<?php

namespace StaticAnalysis\AccessInterceptorScopeLocalizer;

use ProxyManager\Factory\AccessInterceptorScopeLocalizerFactory;

require_once __DIR__ . '/../../vendor/autoload.php';

class MyProxiedClass
{
    public function sayHello() : string
    {
        return 'Hello!';
    }
}

(static function () : void {
    echo (new AccessInterceptorScopeLocalizerFactory())
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

    $localizedAccessInterceptor = (new AccessInterceptorScopeLocalizerFactory())
        ->createProxy(new MyProxiedClass());

    $localizedAccessInterceptor->setMethodPrefixInterceptor(
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

    $localizedAccessInterceptor->setMethodSuffixInterceptor(
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

    echo $localizedAccessInterceptor->sayHello();
})();
