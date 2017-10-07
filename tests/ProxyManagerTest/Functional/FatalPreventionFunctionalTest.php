<?php

declare(strict_types=1);

namespace ProxyManagerTest\Functional;

use PHPUnit\Framework\TestCase;
use ProxyManager\Exception\ExceptionInterface;
use ProxyManager\Generator\ClassGenerator;
use ProxyManager\GeneratorStrategy\EvaluatingGeneratorStrategy;
use ProxyManager\Proxy\ProxyInterface;
use ProxyManager\ProxyGenerator\AccessInterceptorScopeLocalizerGenerator;
use ProxyManager\ProxyGenerator\AccessInterceptorValueHolderGenerator;
use ProxyManager\ProxyGenerator\LazyLoadingGhostGenerator;
use ProxyManager\ProxyGenerator\LazyLoadingValueHolderGenerator;
use ProxyManager\ProxyGenerator\NullObjectGenerator;
use ProxyManager\ProxyGenerator\RemoteObjectGenerator;
use ProxyManager\Signature\ClassSignatureGenerator;
use ProxyManager\Signature\SignatureGenerator;
use ReflectionClass;
use ReflectionException;

/**
 * Verifies that proxy-manager will not attempt to `eval()` code that will cause fatal errors
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 *
 * @group Functional
 * @coversNothing
 */
class FatalPreventionFunctionalTest extends TestCase
{
    /**
     * Verifies that code generation and evaluation will not cause fatals with any given class
     *
     * @param string $generatorClass an instantiable class (no arguments) implementing
     *                               the {@see \ProxyManager\ProxyGenerator\ProxyGeneratorInterface}
     * @param string $className      a valid (existing/autoloadable) class name
     *
     * @dataProvider getTestedClasses
     */
    public function testCodeGeneration(string $generatorClass, string $className) : void
    {
        $generatedClass          = new ClassGenerator(uniqid('generated'));
        $generatorStrategy       = new EvaluatingGeneratorStrategy();
        /* @var $classGenerator \ProxyManager\ProxyGenerator\ProxyGeneratorInterface */
        $classGenerator          = new $generatorClass;
        $classSignatureGenerator = new ClassSignatureGenerator(new SignatureGenerator());

        try {
            $classGenerator->generate(new ReflectionClass($className), $generatedClass);
            $classSignatureGenerator->addSignature($generatedClass, array('eval tests'));
            $generatorStrategy->generate($generatedClass);
        } catch (ExceptionInterface $e) {
            // empty catch: this is actually a supported failure
        } catch (ReflectionException $e) {
            // empty catch: this is actually a supported failure
        }

        self::assertTrue(true, 'Code generation succeeded: proxy is valid or couldn\'t be generated at all');
    }

    /**
     * @return string[][]
     */
    public function getTestedClasses() : array
    {
        $that = $this;

        return call_user_func_array(
            'array_merge',
            array_map(
                function ($generator) use ($that) : array {
                    return array_map(
                        function ($class) use ($generator) : array {
                            return [$generator, $class];
                        },
                        $that->getProxyTestedClasses()
                    );
                },
                [
                    AccessInterceptorScopeLocalizerGenerator::class,
                    AccessInterceptorValueHolderGenerator::class,
                    LazyLoadingGhostGenerator::class,
                    LazyLoadingValueHolderGenerator::class,
                    NullObjectGenerator::class,
                    RemoteObjectGenerator::class,
                ]
            )
        );
    }

    /**
     * @private (public only for PHP 5.3 compatibility)
     *
     * @return string[]
     */
    public function getProxyTestedClasses() : array
    {
        $skippedPaths = [
            realpath(__DIR__ . '/../../src'),
            realpath(__DIR__ . '/../../vendor'),
            realpath(__DIR__ . '/../../tests/ProxyManagerTest'),
        ];

        return array_filter(
            get_declared_classes(),
            function ($className) use ($skippedPaths) : bool {
                $reflectionClass = new ReflectionClass($className);
                $fileName        = $reflectionClass->getFileName();

                if (! $fileName) {
                    return false;
                }

                if ($reflectionClass->implementsInterface(ProxyInterface::class)) {
                    return false;
                }

                $realPath = realpath($fileName);

                foreach ($skippedPaths as $skippedPath) {
                    if (0 === strpos($realPath, $skippedPath)) {
                        // skip classes defined within ProxyManager, vendor or the test suite
                        return false;
                    }
                }

                return true;
            }
        );
    }
}
