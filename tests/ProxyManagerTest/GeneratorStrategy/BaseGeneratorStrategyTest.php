<?php

declare(strict_types=1);

namespace ProxyManagerTest\GeneratorStrategy;

use PHPUnit_Framework_TestCase;
use ProxyManager\Generator\ClassGenerator;
use ProxyManager\Generator\Util\UniqueIdentifierGenerator;
use ProxyManager\GeneratorStrategy\BaseGeneratorStrategy;

/**
 * Tests for {@see \ProxyManager\GeneratorStrategy\BaseGeneratorStrategy}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 *
 * @group Coverage
 */
class BaseGeneratorStrategyTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers \ProxyManager\GeneratorStrategy\BaseGeneratorStrategy::generate
     */
    public function testGenerate() : void
    {
        $strategy       = new BaseGeneratorStrategy();
        $className      = UniqueIdentifierGenerator::getIdentifier('Foo');
        $classGenerator = new ClassGenerator($className);
        $generated      = $strategy->generate($classGenerator);

        self::assertGreaterThan(0, strpos($generated, $className));
    }
}
