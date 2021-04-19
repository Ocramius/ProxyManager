<?php

declare(strict_types=1);

namespace ProxyManagerTest\ProxyGenerator\LazyLoadingGhost\PropertyGenerator;

use Laminas\Code\Generator\PropertyGenerator;
use ProxyManager\ProxyGenerator\LazyLoadingGhost\PropertyGenerator\InitializationTracker;
use ProxyManagerTest\ProxyGenerator\PropertyGenerator\AbstractUniquePropertyNameTest;

/**
 * Tests for {@see \ProxyManager\ProxyGenerator\LazyLoadingGhost\PropertyGenerator\InitializationTracker}
 *
 * @covers \ProxyManager\ProxyGenerator\LazyLoadingGhost\PropertyGenerator\InitializationTracker
 * @group Coverage
 */
final class InitializationTrackerTest extends AbstractUniquePropertyNameTest
{
    protected function createProperty(): PropertyGenerator
    {
        return new InitializationTracker();
    }

    public function testInitializationFlagIsFalseByDefault(): void
    {
        $defaultValue = $this->createProperty()
            ->getDefaultValue();

        self::assertNotNull($defaultValue);
        self::assertFalse($defaultValue->getValue());
    }
}
