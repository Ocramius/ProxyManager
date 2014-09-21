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

namespace ProxyManager\Factory;

use ProxyManager\Configuration;
use ProxyManager\Generator\ClassGenerator;
use ProxyManager\Signature\SignatureGenerator;
use ProxyManager\Signature\ClassSignatureGenerator;
use ProxyManager\Signature\SignatureChecker;
use ReflectionClass;

/**
 * Base factory common logic
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
abstract class AbstractBaseFactory
{
    /**
     * @var \ProxyManager\Configuration
     */
    protected $configuration;

    /**
     * @var \ProxyManager\Inflector\ClassNameInflectorInterface
     */
    protected $inflector;

    /**
     * Cached checked class names
     *
     * @var string[]
     */
    private $checkedClasses = array();

    /**
     * @param \ProxyManager\Configuration $configuration
     */
    public function __construct(Configuration $configuration = null)
    {
        $this->configuration = $configuration ?: new Configuration();
        // localizing some properties for performance
        $this->inflector     = $this->configuration->getClassNameInflector();
    }

    /**
     * Generate a proxy from a class name
     * @param  string $className
     * @return string proxy class name
     */
    protected function generateProxy($className)
    {
        if (isset($this->checkedClasses[$className])) {
            return $this->checkedClasses[$className];
        }

        $proxyParameters = array(
            'className' => $className,
            'factory'   => get_class($this),
        );
        $proxyClassName = $this->inflector->getProxyClassName($className, $proxyParameters);

        if (! class_exists($proxyClassName)) {
            $className = $this->inflector->getUserClassName($className);
            $phpClass  = new ClassGenerator($proxyClassName);

            $this->getGenerator()->generate(new ReflectionClass($className), $phpClass);

            $signatureApplier = new ClassSignatureGenerator(new SignatureGenerator());

            $phpClass = $signatureApplier->addSignature($phpClass, $proxyParameters);

            $this->configuration->getGeneratorStrategy()->generate($phpClass);
            $this->configuration->getProxyAutoloader()->__invoke($proxyClassName);
        }

        $this->checkSignature($proxyClassName, $proxyParameters);

        $this->checkedClasses[] = $proxyClassName;

        return $proxyClassName;
    }

    /**
     * @param string $proxyClassName
     * @param array  $proxyParameters
     */
    private function checkSignature($proxyClassName, array $proxyParameters)
    {
        $signatureChecker = new SignatureChecker(new SignatureGenerator());

        $signatureChecker->checkSignature(new ReflectionClass($proxyClassName), $proxyParameters);
    }

    /**
     * @return \ProxyManager\ProxyGenerator\ProxyGeneratorInterface
     */
    abstract protected function getGenerator();
}
