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

namespace ProxyManager;

use ProxyManager\Autoloader\Autoloader;
use ProxyManager\Autoloader\AutoloaderInterface;
use ProxyManager\FileLocator\FileLocator;
use ProxyManager\GeneratorStrategy\FileWriterGeneratorStrategy;
use ProxyManager\GeneratorStrategy\GeneratorStrategyInterface;
use ProxyManager\Inflector\ClassNameInflector;
use ProxyManager\Inflector\ClassNameInflectorInterface;
use ProxyManager\Signature\ClassSignatureGenerator;
use ProxyManager\Signature\ClassSignatureGeneratorInterface;
use ProxyManager\Signature\SignatureChecker;
use ProxyManager\Signature\SignatureCheckerInterface;
use ProxyManager\Signature\SignatureGenerator;
use ProxyManager\Signature\SignatureGeneratorInterface;

/**
 * Base configuration class for the proxy manager - serves as micro disposable DIC/facade
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
class Configuration
{
    const DEFAULT_PROXY_NAMESPACE = 'ProxyManagerGeneratedProxy';

    /**
     * @var string|null
     */
    protected $proxiesTargetDir;

    /**
     * @var string
     */
    protected $proxiesNamespace = self::DEFAULT_PROXY_NAMESPACE;

    /**
     * @var GeneratorStrategyInterface|null
     */
    protected $generatorStrategy;

    /**
     * @var callable|null
     */
    protected $proxyAutoloader;

    /**
     * @var ClassNameInflectorInterface|null
     */
    protected $classNameInflector;

    /**
     * @var SignatureGeneratorInterface|null
     */
    protected $signatureGenerator;

    /**
     * @var SignatureCheckerInterface|null
     */
    protected $signatureChecker;

    /**
     * @var ClassSignatureGeneratorInterface|null
     */
    protected $classSignatureGenerator;

    /**
     * @deprecated deprecated since version 0.5
     * @codeCoverageIgnore
     */
    public function setAutoGenerateProxies()
    {
    }

    /**
     * @return bool
     *
     * @deprecated deprecated since version 0.5
     * @codeCoverageIgnore
     */
    public function doesAutoGenerateProxies()
    {
        return true;
    }

    /**
     * @param AutoloaderInterface $proxyAutoloader
     */
    public function setProxyAutoloader(AutoloaderInterface $proxyAutoloader)
    {
        $this->proxyAutoloader = $proxyAutoloader;
    }

    /**
     * @return AutoloaderInterface
     */
    public function getProxyAutoloader()
    {
        return $this->proxyAutoloader
            ?: $this->proxyAutoloader = new Autoloader(
                new FileLocator($this->getProxiesTargetDir()),
                $this->getClassNameInflector()
            );
    }

    /**
     * @param string $proxiesNamespace
     */
    public function setProxiesNamespace($proxiesNamespace)
    {
        $this->proxiesNamespace = $proxiesNamespace;
    }

    /**
     * @return string
     */
    public function getProxiesNamespace()
    {
        return $this->proxiesNamespace;
    }

    /**
     * @param string $proxiesTargetDir
     */
    public function setProxiesTargetDir($proxiesTargetDir)
    {
        $this->proxiesTargetDir = (string) $proxiesTargetDir;
    }

    /**
     * @return string
     */
    public function getProxiesTargetDir()
    {
        return $this->proxiesTargetDir ?: $this->proxiesTargetDir = sys_get_temp_dir();
    }

    /**
     * @param GeneratorStrategyInterface $generatorStrategy
     */
    public function setGeneratorStrategy(GeneratorStrategyInterface $generatorStrategy)
    {
        $this->generatorStrategy = $generatorStrategy;
    }

    /**
     * @return GeneratorStrategyInterface
     */
    public function getGeneratorStrategy()
    {
        return $this->generatorStrategy
            ?: $this->generatorStrategy = new FileWriterGeneratorStrategy(
                new FileLocator($this->getProxiesTargetDir())
            );
    }

    /**
     * @param ClassNameInflectorInterface $classNameInflector
     */
    public function setClassNameInflector(ClassNameInflectorInterface $classNameInflector)
    {
        $this->classNameInflector = $classNameInflector;
    }

    /**
     * @return ClassNameInflectorInterface
     */
    public function getClassNameInflector()
    {
        return $this->classNameInflector
            ?: $this->classNameInflector = new ClassNameInflector($this->getProxiesNamespace());
    }

    /**
     * @param SignatureGeneratorInterface $signatureGenerator
     */
    public function setSignatureGenerator(SignatureGeneratorInterface $signatureGenerator)
    {
        $this->signatureGenerator = $signatureGenerator;
    }

    /**
     * @return SignatureGeneratorInterface
     */
    public function getSignatureGenerator()
    {
        return $this->signatureGenerator ?: $this->signatureGenerator = new SignatureGenerator();
    }

    /**
     * @param SignatureCheckerInterface $signatureChecker
     */
    public function setSignatureChecker(SignatureCheckerInterface $signatureChecker)
    {
        $this->signatureChecker = $signatureChecker;
    }

    /**
     * @return SignatureCheckerInterface
     */
    public function getSignatureChecker()
    {
        return $this->signatureChecker
            ?: $this->signatureChecker = new SignatureChecker($this->getSignatureGenerator());
    }

    /**
     * @param ClassSignatureGeneratorInterface $classSignatureGenerator
     */
    public function setClassSignatureGenerator(ClassSignatureGeneratorInterface $classSignatureGenerator)
    {
        $this->classSignatureGenerator = $classSignatureGenerator;
    }

    /**
     * @return ClassSignatureGeneratorInterface
     */
    public function getClassSignatureGenerator()
    {
        return $this->classSignatureGenerator
            ?: new ClassSignatureGenerator($this->getSignatureGenerator());
    }
}
