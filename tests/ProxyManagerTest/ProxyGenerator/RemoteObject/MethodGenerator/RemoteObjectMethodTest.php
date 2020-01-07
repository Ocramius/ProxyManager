<?php

declare(strict_types=1);

namespace ProxyManagerTest\ProxyGenerator\RemoteObject\MethodGenerator;

use Laminas\Code\Generator\PropertyGenerator;
use Laminas\Code\Reflection\MethodReflection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ProxyManager\ProxyGenerator\RemoteObject\MethodGenerator\RemoteObjectMethod;
use ProxyManagerTestAsset\BaseClass;
use ReflectionClass;

/**
 * Tests for {@see \ProxyManager\ProxyGenerator\RemoteObject\MethodGenerator\RemoteObjectMethod}
 *
 * @group Coverage
 */
final class RemoteObjectMethodTest extends TestCase
{
    /**
     * @covers \ProxyManager\ProxyGenerator\RemoteObject\MethodGenerator\RemoteObjectMethod
     */
    public function testBodyStructureWithParameters() : void
    {
        $adapter = $this->createMock(PropertyGenerator::class);
        $adapter->method('getName')->willReturn('adapter');

        $reflectionMethod = new MethodReflection(
            BaseClass::class,
            'publicByReferenceParameterMethod'
        );

        $method = RemoteObjectMethod::generateMethod(
            $reflectionMethod,
            $adapter,
            new ReflectionClass(PropertyGenerator::class)
        );

        self::assertSame('publicByReferenceParameterMethod', $method->getName());
        self::assertCount(2, $method->getParameters());
        self::assertSame(
            '$defaultValues = array (
  0 => NULL,
  1 => NULL,
);
$declaredParameterCount = 2;

$args = \func_get_args() + $defaultValues;

$return = $this->adapter->call(\'Laminas\\\\Code\\\\Generator\\\\PropertyGenerator\', \'publicByReferenceParameterMethod\', $args);

return $return;',
            $method->getBody()
        );
    }

    /**
     * @covers \ProxyManager\ProxyGenerator\RemoteObject\MethodGenerator\RemoteObjectMethod
     */
    public function testBodyStructureWithArrayParameter() : void
    {
        $adapter = $this->createMock(PropertyGenerator::class);
        $adapter->method('getName')->willReturn('adapter');

        $reflectionMethod = new MethodReflection(BaseClass::class, 'publicArrayHintedMethod');

        $method = RemoteObjectMethod::generateMethod(
            $reflectionMethod,
            $adapter,
            new ReflectionClass(PropertyGenerator::class)
        );

        self::assertSame('publicArrayHintedMethod', $method->getName());
        self::assertCount(1, $method->getParameters());
        self::assertSame(
            "\$defaultValues = array (
  0 => NULL,
);
\$declaredParameterCount = 1;

\$args = \\func_get_args() + \$defaultValues;

\$return = \$this->adapter->call('Laminas\\\\Code\\\\Generator\\\\PropertyGenerator', 'publicArrayHintedMethod', \$args);

return \$return;",
            $method->getBody()
        );
    }

    /**
     * @covers \ProxyManager\ProxyGenerator\RemoteObject\MethodGenerator\RemoteObjectMethod
     */
    public function testBodyStructureWithoutParameters() : void
    {
        $adapter = $this->createMock(PropertyGenerator::class);
        $adapter->method('getName')->willReturn('adapter');

        $reflectionMethod = new MethodReflection(BaseClass::class, 'publicMethod');

        $method = RemoteObjectMethod::generateMethod(
            $reflectionMethod,
            $adapter,
            new ReflectionClass(PropertyGenerator::class)
        );

        self::assertSame('publicMethod', $method->getName());
        self::assertCount(0, $method->getParameters());
        self::assertSame(
            "\$defaultValues = array (
);
\$declaredParameterCount = 0;

\$args = \\func_get_args() + \$defaultValues;

\$return = \$this->adapter->call('Laminas\\\\Code\\\\Generator\\\\PropertyGenerator', 'publicMethod', \$args);

return \$return;",
            $method->getBody()
        );
    }
}
