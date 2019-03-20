<?php

declare(strict_types=1);

namespace ProxyManagerTest\GeneratorStrategy;

use PHPUnit\Framework\TestCase;
use ProxyManager\Generator\ClassGenerator;
use ProxyManager\Generator\Util\UniqueIdentifierGenerator;
use ProxyManager\GeneratorStrategy\BaseGeneratorStrategy;
use function strpos;

/**
 * Tests for {@see \ProxyManager\GeneratorStrategy\BaseGeneratorStrategy}
 *
 * @group Coverage
 */
final class BaseGeneratorStrategyTest extends TestCase
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
