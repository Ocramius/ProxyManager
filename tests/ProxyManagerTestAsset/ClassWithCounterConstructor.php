<?php

declare(strict_types=1);

namespace ProxyManagerTestAsset;

/**
 * Class with a constructor that implements an internal counter: used to verify if proxies
 * behave like normal objects when instantiated manually
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
class ClassWithCounterConstructor
{
    /**
     * @var int
     */
    public $amount = 0;

    /**
     * @param int $increment
     */
    public function __construct($increment)
    {
        $this->amount += $increment;
    }

    /**
     * @return int
     */
    public function getAmount()
    {
        return $this->amount;
    }
}
