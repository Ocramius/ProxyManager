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

use ProxyManager\Exception\FileNotWritableException;
use ProxyManager\FileLocator\FileLocatorInterface;
use Zend\Code\Generator\ClassGenerator;

/**
 * Generator strategy that writes the generated classes to disk while generating them
 *
 * {@inheritDoc}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
class FileWriterGeneratorStrategy implements GeneratorStrategyInterface
{
    /**
     * @var \ProxyManager\FileLocator\FileLocatorInterface
     */
    protected $fileLocator;

    /**
     * @var callable
     */
    private $emptyErrorHandler;

    /**
     * @param \ProxyManager\FileLocator\FileLocatorInterface $fileLocator
     */
    public function __construct(FileLocatorInterface $fileLocator)
    {
        $this->fileLocator       = $fileLocator;
        $this->emptyErrorHandler = function () {
        };
    }

    /**
     * Write generated code to disk and return the class code
     *
     * {@inheritDoc}
     */
    public function generate(ClassGenerator $classGenerator) : string
    {
        $className     = trim($classGenerator->getNamespaceName(), '\\')
            . '\\' . trim($classGenerator->getName(), '\\');
        $generatedCode = $classGenerator->generate();
        $fileName      = $this->fileLocator->getProxyFileName($className);

        set_error_handler($this->emptyErrorHandler);

        try {
            $this->writeFile("<?php\n\n" . $generatedCode, $fileName);
        } catch (FileNotWritableException $fileNotWritable) {
            throw $fileNotWritable;
        } finally {
            restore_error_handler();
        }

        return $generatedCode;
    }

    /**
     * Writes the source file in such a way that race conditions are avoided when the same file is written
     * multiple times in a short time period
     *
     * @param string $source
     * @param string $location
     *
     * @return void
     *
     * @throws FileNotWritableException
     */
    private function writeFile(string $source, string $location)
    {
        $tmpFileName   = $location . '.' . uniqid('', true);

        if (! file_put_contents($tmpFileName, $source)) {
            throw FileNotWritableException::fromNonWritableLocation($tmpFileName);
        }

        if (! rename($tmpFileName, $location)) {
            unlink($tmpFileName);

            throw FileNotWritableException::fromInvalidMoveOperation($tmpFileName, $location);
        }
    }
}
