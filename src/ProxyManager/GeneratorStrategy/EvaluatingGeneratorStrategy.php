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

declare(strict_types=1);

namespace ProxyManager\GeneratorStrategy;

use ProxyManager\Configuration;
use Zend\Code\Generator\ClassGenerator;

/**
 * Generator strategy that produces the code and evaluates it at runtime
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
class EvaluatingGeneratorStrategy implements GeneratorStrategyInterface
{
    /**
     * @var bool flag indicating whether {@see eval} can be used
     */
    private $canEval = true;

    /**
     * Constructor
     */
    public function __construct()
    {
        // @codeCoverageIgnoreStart
        $this->canEval = ! ini_get('suhosin.executor.disable_eval');
        // @codeCoverageIgnoreEnd
    }

    /**
     * Evaluates the generated code before returning it
     *
     * {@inheritDoc}
     */
    public function generate(ClassGenerator $classGenerator) : string
    {
        $code = $classGenerator->generate();

        // @codeCoverageIgnoreStart
        if (! $this->canEval) {
            $fileName = tempnam(sys_get_temp_dir(), 'EvaluatingGeneratorStrategy.php.tmp.');

            file_put_contents($fileName, "<?php\n" . $code);
            /* @noinspection PhpIncludeInspection */
            require $fileName;
            unlink($fileName);

            return $code;
        }
        // @codeCoverageIgnoreEnd

        eval($code);

        return $code;
    }

    /**
     * {@inheritdoc}
     */
    public function classExists(string $proxyClassName, Configuration $configuration): bool
    {
        return class_exists($proxyClassName);
    }
}
