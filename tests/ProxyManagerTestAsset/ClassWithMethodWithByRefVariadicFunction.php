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
     * @param mixed & ...$fooz
     *
     * @return mixed[]
     */
    public function tuz(& ...$fooz)
    {
        $fooz[1] = 'changed';

        return $fooz;
    }
}
