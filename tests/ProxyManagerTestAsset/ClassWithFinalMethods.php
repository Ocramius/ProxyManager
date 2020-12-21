<?php

declare(strict_types=1);

namespace ProxyManagerTestAsset;

use PHPUnit\Framework\TestCase;

/**
 * Base test class to play around with final pre-existing methods
 *
 * @author Jefersson Nathan <malukenho@phpse.net>
 * @license MIT
 */
class ClassWithFinalMethods extends TestCase
{
    final public function foo()
    {
    }

    private function bar()
    {
    }

    final protected function baz()
    {
    }
}
