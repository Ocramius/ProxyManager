<?php

declare(strict_types=1);

namespace ProxyManagerTestAsset;

use PHPUnit_Framework_TestCase;

/**
 * Base test class to play around with final pre-existing methods
 *
 * @author Jefersson Nathan <malukenho@phpse.net>
 * @license MIT
 */
class ClassWithFinalMethods extends PHPUnit_Framework_TestCase
{
    final public function foo()
    {
    }

    final private function bar()
    {
    }

    final protected function baz()
    {
    }
}
