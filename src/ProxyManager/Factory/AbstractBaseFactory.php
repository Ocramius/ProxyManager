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
use ProxyManager\Version;
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
            'className'           => $className,
            'factory'             => get_class($this),
            'proxyManagerVersion' => Version::VERSION
        );
        $proxyClassName  = $this
            ->configuration
            ->getClassNameInflector()
            ->getProxyClassName($className, $proxyParameters);

        if (! class_exists($proxyClassName)) {
            $this->generateProxyClass($proxyClassName, $className, $proxyParameters);
        }

        $this
            ->configuration
            ->getSignatureChecker()
            ->checkSignature(new ReflectionClass($proxyClassName), $proxyParameters);

        return $this->checkedClasses[$className] = $proxyClassName;
    }

    /**
     * @return \ProxyManager\ProxyGenerator\ProxyGeneratorInterface
     */
    abstract protected function getGenerator();

    /**
     * Generates the provided `$proxyClassName` from the given `$className` and `$proxyParameters`
     * @param string $proxyClassName
     * @param string $className
     * @param array  $proxyParameters
     *
     * @return void
     */
    private function generateProxyClass($proxyClassName, $className, array $proxyParameters)
    {
        $className = $this->configuration->getClassNameInflector()->getUserClassName($className);
        $phpClass  = new ClassGenerator($proxyClassName);

        $this->getGenerator()->generate(new ReflectionClass($className), $phpClass);

        $phpClass = $this->configuration->getClassSignatureGenerator()->addSignature($phpClass, $proxyParameters);

        $this->configuration->getGeneratorStrategy()->generate($phpClass);
        $this->configuration->getProxyAutoloader()->__invoke($proxyClassName);
    }
}
