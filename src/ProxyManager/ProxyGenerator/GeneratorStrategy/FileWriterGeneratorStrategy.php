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

namespace ProxyManager\ProxyGenerator\GeneratorStrategy;

use CG\Core\DefaultGeneratorStrategy;
use CG\Generator\PhpClass;
use ProxyManager\ProxyGenerator\FileLocator\FileLocatorInterface;

/**
 * Generator strategy that writes the generated classes to disk, and includes them
 *
 * {@inheritDoc}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
class FileWriterGeneratorStrategy extends DefaultGeneratorStrategy
{
    /**
     * @var \ProxyManager\ProxyGenerator\FileLocator\FileLocatorInterface
     */
    protected $fileLocator;

    /**
     * @param \ProxyManager\ProxyGenerator\FileLocator\FileLocatorInterface $fileLocator
     */
    public function __construct(FileLocatorInterface $fileLocator)
    {
        $this->fileLocator = $fileLocator;
    }

    /**
     * Write generated code to disk and return the class code
     *
     * {@inheritDoc}
     */
    public function generate(PhpClass $class)
    {
        $generatedCode = parent::generate($class);
        $fileName      = $this->fileLocator->getProxyFileName($class->getName());
        $tmpFileName   = $fileName . '.' . uniqid('', true);

        file_put_contents($tmpFileName, "<?php\n\n" . $generatedCode);
        rename($tmpFileName, $fileName);

        return $generatedCode;
    }
}