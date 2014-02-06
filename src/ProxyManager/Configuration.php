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

use ProxyManager\Autoloader\AutoloaderInterface;
use ProxyManager\Autoloader\Autoloader;
use ProxyManager\FileLocator\FileLocator;
use ProxyManager\GeneratorStrategy\FileWriterGeneratorStrategy;
use ProxyManager\GeneratorStrategy\GeneratorStrategyInterface;
use ProxyManager\Inflector\ClassNameInflectorInterface;
use ProxyManager\Inflector\ClassNameInflector;

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
     * @var \ProxyManager\GeneratorStrategy\GeneratorStrategyInterface|null
     */
    protected $generatorStrategy;

    /**
     * @var callable|null
     */
    protected $proxyAutoloader;

    /**
     * @var \ProxyManager\Inflector\ClassNameInflectorInterface|null
     */
    protected $classNameInflector;

    /**
     * @deprecated deprecated since version 0.5
     */
    public function setAutoGenerateProxies()
    {
    }

    /**
     * @return bool
     *
     * @deprecated deprecated since version 0.5
     */
    public function doesAutoGenerateProxies()
    {
        return true;
    }

    /**
     * @param \ProxyManager\Autoloader\AutoloaderInterface $proxyAutoloader
     */
    public function setProxyAutoloader(AutoloaderInterface $proxyAutoloader)
    {
        $this->proxyAutoloader = $proxyAutoloader;
    }

    /**
     * @return \ProxyManager\Autoloader\AutoloaderInterface
     */
    public function getProxyAutoloader()
    {
        if (null === $this->proxyAutoloader) {
            $this->proxyAutoloader = new Autoloader(
                new FileLocator($this->getProxiesTargetDir()),
                $this->getClassNameInflector()
            );
        }

        return $this->proxyAutoloader;
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
     * @return null|string
     */
    public function getProxiesTargetDir()
    {
        if (null === $this->proxiesTargetDir) {
            $this->proxiesTargetDir = sys_get_temp_dir();
        }

        return $this->proxiesTargetDir;
    }

    /**
     * @param \ProxyManager\GeneratorStrategy\GeneratorStrategyInterface $generatorStrategy
     */
    public function setGeneratorStrategy(GeneratorStrategyInterface $generatorStrategy)
    {
        $this->generatorStrategy = $generatorStrategy;
    }

    /**
     * @return \ProxyManager\GeneratorStrategy\GeneratorStrategyInterface
     */
    public function getGeneratorStrategy()
    {
        if (null === $this->generatorStrategy) {
            $this->generatorStrategy = new FileWriterGeneratorStrategy(new FileLocator($this->getProxiesTargetDir()));
        }

        return $this->generatorStrategy;
    }

    /**
     * @param \ProxyManager\Inflector\ClassNameInflectorInterface $classNameInflector
     */
    public function setClassNameInflector(ClassNameInflectorInterface $classNameInflector)
    {
        $this->classNameInflector = $classNameInflector;
    }

    /**
     * @return \ProxyManager\Inflector\ClassNameInflectorInterface
     */
    public function getClassNameInflector()
    {
        if (null === $this->classNameInflector) {
            $this->classNameInflector = new ClassNameInflector($this->getProxiesNamespace());
        }

        return $this->classNameInflector;
    }
}
