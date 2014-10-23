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

$factory = new %s;

try {
    $factory->createProxy(%s, function () {});
} catch (\ProxyManager\Exception\ExceptionInterface $e) {
}

echo 'SUCCESS: ' . %s;
PHP;

    /**
     * Verifies that lazy loading ghost creation will work with all given classes
     *
     * @param string $className a valid (existing/autoloadable) class name
     *
     * @dataProvider getTestedClasses
     */
    public function testLazyLoadingGhost($className)
    {
        $runner = PHPUnit_Util_PHP::factory();

        $code = sprintf(
            $this->template,
            var_export(realpath(__DIR__ . '/../../../vendor/autoload.php'), true),
            'ProxyManager\\Factory\\LazyLoadingGhostFactory',
            var_export($className, true),
            var_export($className, true)
        );

        $result = $runner->runJob($code);

        if (('SUCCESS: ' . $className) !== $result['stdout']) {
            $this->fail(sprintf(
                "Crashed with class '%s'.\n\nStdout:\n%s\nStderr:\n%s\nGenerated code:\n%s'",
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
        return array_map(
            function ($className) {
                return array($className);
            },
            get_declared_classes()
        );
    }
}
