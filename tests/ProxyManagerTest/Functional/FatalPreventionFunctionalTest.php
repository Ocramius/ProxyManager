<?php
/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license.
 */

namespace ProxyManagerTest\Functional;

use PHPUnit_Framework_TestCase;
use PHPUnit_Util_PHP;
use ReflectionClass;

/**
 * Verifies that proxy-manager will not attempt to `eval()` code that will cause fatal errors
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 *
 * @group Functional
 * @coversNothing
 */
class FatalPreventionFunctionalTest extends PHPUnit_Framework_TestCase
{
    private $template = <<<'PHP'
<?php

require_once %s;

$className               = %s;
$generatedClass          = new ProxyManager\Generator\ClassGenerator(uniqid('generated'));
$generatorStrategy       = new ProxyManager\GeneratorStrategy\EvaluatingGeneratorStrategy();
$classGenerator          = new %s;
$classSignatureGenerator = new ProxyManager\Signature\ClassSignatureGenerator(
    new ProxyManager\Signature\SignatureGenerator()
);

try {
    $classGenerator->generate(new ReflectionClass($className), $generatedClass);
    $classSignatureGenerator->addSignature($generatedClass, array('eval tests'));
    $generatorStrategy->generate($generatedClass);
} catch (ProxyManager\Exception\ExceptionInterface $e) {
} catch (ReflectionException $e) {
}

echo 'SUCCESS: ' . %s;
PHP;

    /**
     * Verifies that code generation and evaluation will not cause fatals with any given class
     *
     * @param string $generatorClass an instantiable class (no arguments) implementing
     *                               the {@see \ProxyManager\ProxyGenerator\ProxyGeneratorInterface}
     * @param string $className      a valid (existing/autoloadable) class name
     *
     * @dataProvider getTestedClasses
     */
    public function testCodeGeneration($generatorClass, $className)
    {
        if (defined('HHVM_VERSION')) {
            $this->markTestSkipped('HHVM is just too slow for this kind of test right now.');
        }

        if (PHP_VERSION_ID < 50401) {
            $this->markTestSkipped('Can\'t run this test suite on php < 5.4.1');
        }

        $runner = PHPUnit_Util_PHP::factory();

        $code = sprintf(
            $this->template,
            var_export(realpath(__DIR__ . '/../../../vendor/autoload.php'), true),
            var_export($className, true),
            $generatorClass,
            var_export($className, true)
        );

        $result = $runner->runJob($code, array('-n'));

        if (('SUCCESS: ' . $className) !== $result['stdout']) {
            $this->fail(sprintf(
                "Crashed with class '%s' and generator '%s'.\n\nStdout:\n%s\nStderr:\n%s\nGenerated code:\n%s'",
                $generatorClass,
                $className,
                $result['stdout'],
                $result['stderr'],
                $code
            ));
        }

        $this->assertSame('SUCCESS: ' . $className, $result['stdout']);
    }

    /**
     * @return string[][]
     */
    public function getTestedClasses()
    {
        $that = $this;

        return call_user_func_array(
            'array_merge',
            array_map(
                function ($generator) use ($that) {
                    return array_map(
                        function ($class) use ($generator) {
                            return array($generator, $class);
                        },
                        $that->getProxyTestedClasses()
                    );
                },
                array(
                    'ProxyManager\\ProxyGenerator\\AccessInterceptorScopeLocalizerGenerator',
                    'ProxyManager\\ProxyGenerator\\AccessInterceptorValueHolderGenerator',
                    'ProxyManager\\ProxyGenerator\\LazyLoadingGhostGenerator',
                    'ProxyManager\\ProxyGenerator\\LazyLoadingValueHolderGenerator',
                    'ProxyManager\\ProxyGenerator\\NullObjectGenerator',
                    'ProxyManager\\ProxyGenerator\\RemoteObjectGenerator',
                )
            )
        );
    }

    /**
     * @private (public only for PHP 5.3 compatibility)
     *
     * @return string[]
     */
    public function getProxyTestedClasses()
    {
        $skippedPaths = array(
            realpath(__DIR__ . '/../../src'),
            realpath(__DIR__ . '/../../vendor'),
            realpath(__DIR__ . '/../../tests/ProxyManagerTest'),
        );

        return array_filter(
            get_declared_classes(),
            function ($className) use ($skippedPaths) {
                $reflectionClass = new ReflectionClass($className);
                $fileName        = $reflectionClass->getFileName();

                if (! $fileName) {
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
