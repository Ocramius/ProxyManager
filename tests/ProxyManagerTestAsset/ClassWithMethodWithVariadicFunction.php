<?php

declare(strict_types=1);

namespace ProxyManagerTestAsset;

/**
 * Test asset class with method with variadic parameter
 *
 * @author Jefersson Nathan <malukenho@phpse.net>
 * @license MIT
 */
class ClassWithMethodWithVariadicFunction
{
    /**
     * @var mixed
     */
    public $bar;

    /**
     * @var mixed
     */
    public $baz;

    /**
     * @param mixed $bar
     * @param mixed $baz
     */
    public function foo($bar, ...$baz)
    {
        $this->bar = $bar;
        $this->baz = $baz;
    }

    /**
     * @param mixed ...$fooz
     *
     * @return mixed[]
     */
    public function buz(...$fooz)
    {
        return $fooz;
    }
}
