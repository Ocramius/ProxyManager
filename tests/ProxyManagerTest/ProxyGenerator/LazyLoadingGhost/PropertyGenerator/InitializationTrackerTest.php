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
    /**
     * {@inheritDoc}
     */
    protected function createProperty() : PropertyGenerator
    {
        return new InitializationTracker();
    }

    public function testInitializationFlagIsFalseByDefault() : void
    {
        $property = $this->createProperty();

        self::assertFalse($property->getDefaultValue()->getValue());
    }
}
