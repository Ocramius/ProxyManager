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

namespace ProxyManager;

use ProxyManager\Autoloader\Autoloader;
use ProxyManager\Autoloader\AutoloaderInterface;
use ProxyManager\FileLocator\FileLocator;
use ProxyManager\GeneratorStrategy\EvaluatingGeneratorStrategy;
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

    public function setProxyAutoloader(AutoloaderInterface $proxyAutoloader) : void
    {
        $this->proxyAutoloader = $proxyAutoloader;
    }

    public function getProxyAutoloader() : AutoloaderInterface
    {
        return $this->proxyAutoloader
            ?: $this->proxyAutoloader = new Autoloader(
                new FileLocator($this->getProxiesTargetDir()),
                $this->getClassNameInflector()
            );
    }

    public function setProxiesNamespace(string $proxiesNamespace) : void
    {
        $this->proxiesNamespace = $proxiesNamespace;
    }

    public function getProxiesNamespace() : string
    {
        return $this->proxiesNamespace;
    }

    public function setProxiesTargetDir(string $proxiesTargetDir) : void
    {
        $this->proxiesTargetDir = $proxiesTargetDir;
    }

    public function getProxiesTargetDir() : string
    {
        return $this->proxiesTargetDir ?: $this->proxiesTargetDir = sys_get_temp_dir();
    }

    public function setGeneratorStrategy(GeneratorStrategyInterface $generatorStrategy) : void
    {
        $this->generatorStrategy = $generatorStrategy;
    }

    public function getGeneratorStrategy() : GeneratorStrategyInterface
    {
        return $this->generatorStrategy
            ?: $this->generatorStrategy = new EvaluatingGeneratorStrategy();
    }

    public function setClassNameInflector(ClassNameInflectorInterface $classNameInflector) : void
    {
        $this->classNameInflector = $classNameInflector;
    }

    public function getClassNameInflector() : ClassNameInflectorInterface
    {
        return $this->classNameInflector
            ?: $this->classNameInflector = new ClassNameInflector($this->getProxiesNamespace());
    }

    public function setSignatureGenerator(SignatureGeneratorInterface $signatureGenerator) : void
    {
        $this->signatureGenerator = $signatureGenerator;
    }

    public function getSignatureGenerator() : SignatureGeneratorInterface
    {
        return $this->signatureGenerator ?: $this->signatureGenerator = new SignatureGenerator();
    }

    public function setSignatureChecker(SignatureCheckerInterface $signatureChecker) : void
    {
        $this->signatureChecker = $signatureChecker;
    }

    public function getSignatureChecker() : SignatureCheckerInterface
    {
        return $this->signatureChecker
            ?: $this->signatureChecker = new SignatureChecker($this->getSignatureGenerator());
    }

    public function setClassSignatureGenerator(ClassSignatureGeneratorInterface $classSignatureGenerator) : void
    {
        $this->classSignatureGenerator = $classSignatureGenerator;
    }

    public function getClassSignatureGenerator() : ClassSignatureGeneratorInterface
    {
        return $this->classSignatureGenerator
            ?: new ClassSignatureGenerator($this->getSignatureGenerator());
    }
}
