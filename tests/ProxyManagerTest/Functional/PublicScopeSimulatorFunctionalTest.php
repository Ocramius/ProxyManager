<?php

declare(strict_types=1);

namespace ProxyManagerTest\ProxyGenerator\Functional;

use PHPUnit\Framework\TestCase;
use ProxyManager\ProxyGenerator\Util\PublicScopeSimulator;
use ProxyManagerTestAsset\ClassWithMixedProperties;

use function sprintf;

/**
 * @covers \ProxyManager\ProxyGenerator\Util\PublicScopeSimulator
 * @coversNothing
 */
final class PublicScopeSimulatorFunctionalTest extends TestCase
{
    /**
     * @group #632
     * @group #645
     * @group #646
     */
    public function testAccessingUndefinedPropertiesDoesNotLeadToInvalidByRefAccess(): void
    {
        /** @psalm-var ClassWithMixedProperties $sut */
        $sut = eval(sprintf(
            <<<'PHP'
return new class() extends %s {
    public function doGet($prop) : string { %s }
    public function doSet($prop, $val) : string { %s }
    public function doIsset($prop) : bool { %s }
    public function doUnset($prop) : void { %s }
};
PHP
            ,
            ClassWithMixedProperties::class,
            PublicScopeSimulator::getPublicAccessSimulationCode(PublicScopeSimulator::OPERATION_GET, 'prop'),
            PublicScopeSimulator::getPublicAccessSimulationCode(PublicScopeSimulator::OPERATION_SET, 'prop', 'val'),
            PublicScopeSimulator::getPublicAccessSimulationCode(PublicScopeSimulator::OPERATION_ISSET, 'prop'),
            PublicScopeSimulator::getPublicAccessSimulationCode(PublicScopeSimulator::OPERATION_UNSET, 'prop')
        ));

        self::assertSame('publicProperty0', $sut->doGet('publicProperty0'));
        self::assertSame('bar', $sut->doSet('publicProperty0', 'bar'));
        self::assertTrue($sut->doIsset('publicProperty0'));
        self::assertNull($sut->doUnset('publicProperty0'));
    }
}
