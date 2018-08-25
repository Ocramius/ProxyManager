<?php

declare(strict_types=1);

namespace ProxyManagerTestAsset;

/**
 * Test asset class with method with by-ref variadic parameter
 *
 * @author Jefersson Nathan <malukenho@phpse.net>
 * @license MIT
 */
class ClassWithMethodWithByRefVariadicFunction
{
    /**
     * @param array<int, mixed> $fooz
     *
     * @return array<int, mixed>
     */
    public function tuz(& ...$fooz)
    {
        $fooz[1] = 'changed';

        return $fooz;
    }
}
