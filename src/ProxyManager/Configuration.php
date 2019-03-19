<?php

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
use function sys_get_temp_dir;

/**
 * Base configuration class for the proxy manager - serves as micro disposable DIC/facade
 */
class Configuration
{
    public const DEFAULT_PROXY_NAMESPACE = 'ProxyManagerGeneratedProxy';

    protected ?string $proxiesTargetDir;
    protected string $proxiesNamespace = self::DEFAULT_PROXY_NAMESPACE;
    protected ?GeneratorStrategyInterface $generatorStrategy;
    protected ?AutoloaderInterface $proxyAutoloader;
    protected ?ClassNameInflectorInterface $classNameInflector;
    protected ?SignatureGeneratorInterface $signatureGenerator;
    protected ?SignatureCheckerInterface $signatureChecker;
    protected ?ClassSignatureGeneratorInterface $classSignatureGenerator;

    public function setProxyAutoloader(AutoloaderInterface $proxyAutoloader) : void
    {
        $this->proxyAutoloader = $proxyAutoloader;
    }

    public function getProxyAutoloader() : AutoloaderInterface
    {
        return $this->proxyAutoloader
            ?? $this->proxyAutoloader = new Autoloader(
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
        return $this->proxiesTargetDir
            ?? $this->proxiesTargetDir = sys_get_temp_dir();
    }

    public function setGeneratorStrategy(GeneratorStrategyInterface $generatorStrategy) : void
    {
        $this->generatorStrategy = $generatorStrategy;
    }

    public function getGeneratorStrategy() : GeneratorStrategyInterface
    {
        return $this->generatorStrategy
            ?? $this->generatorStrategy = new EvaluatingGeneratorStrategy();
    }

    public function setClassNameInflector(ClassNameInflectorInterface $classNameInflector) : void
    {
        $this->classNameInflector = $classNameInflector;
    }

    public function getClassNameInflector() : ClassNameInflectorInterface
    {
        return $this->classNameInflector
            ?? $this->classNameInflector = new ClassNameInflector($this->getProxiesNamespace());
    }

    public function setSignatureGenerator(SignatureGeneratorInterface $signatureGenerator) : void
    {
        $this->signatureGenerator = $signatureGenerator;
    }

    public function getSignatureGenerator() : SignatureGeneratorInterface
    {
        return $this->signatureGenerator
            ?? $this->signatureGenerator = new SignatureGenerator();
    }

    public function setSignatureChecker(SignatureCheckerInterface $signatureChecker) : void
    {
        $this->signatureChecker = $signatureChecker;
    }

    public function getSignatureChecker() : SignatureCheckerInterface
    {
        return $this->signatureChecker
            ?? $this->signatureChecker = new SignatureChecker($this->getSignatureGenerator());
    }

    public function setClassSignatureGenerator(ClassSignatureGeneratorInterface $classSignatureGenerator) : void
    {
        $this->classSignatureGenerator = $classSignatureGenerator;
    }

    public function getClassSignatureGenerator() : ClassSignatureGeneratorInterface
    {
        return $this->classSignatureGenerator
            ?? $this->classSignatureGenerator = new ClassSignatureGenerator($this->getSignatureGenerator());
    }
}
