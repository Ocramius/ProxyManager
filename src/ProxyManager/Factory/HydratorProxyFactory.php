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
use ProxyManager\ProxyGenerator\HydratorProxyGenerator;
use ReflectionClass;

/**
 * Factory responsible of producing hydrator proxies
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
class HydratorProxyFactory
{
    /**
     * @var \ProxyManager\Configuration
     */
    protected $configuration;

    /**
     * @var bool
     */
    protected $autoGenerate;

    /**
     * @var \ProxyManager\Inflector\ClassNameInflectorInterface
     */
    protected $inflector;

    /**
     * Cached proxy class names
     *
     * @var string[]
     */
    private $hydratorClassNames = array();

    /**
     * Cached reflection properties
     *
     * @var \ReflectionProperty[][]
     */
    private $reflectionProperties = array();

    /**
     * @param \ProxyManager\Configuration $configuration
     */
    public function __construct(Configuration $configuration)
    {
        $this->configuration = $configuration;
        // localizing some properties for performance
        $this->autoGenerate  = $this->configuration->doesAutoGenerateProxies();
        $this->inflector     = $this->configuration->getClassNameInflector();
    }

    /**
     * @param string $className
     *
     * @return mixed
     */
    public function createProxy($className)
    {
        if (isset($this->hydratorClassNames[$className])) {
            $proxyClassName = $this->hydratorClassNames[$className];

            return new $proxyClassName($this->reflectionProperties[$className]);
        }

        $reflection     = new ReflectionClass($this->inflector->getUserClassName($className));
        $proxyClassName = $this->inflector->getProxyClassName($reflection->getName());

        if ($this->autoGenerate && ! class_exists($proxyClassName)) {
            $classGenerator = new ClassGenerator($proxyClassName);
            $generator      = new HydratorProxyGenerator();

            $generator->generate($reflection, $classGenerator);
            $this->configuration->getGeneratorStrategy()->generate($classGenerator);
            $this->configuration->getProxyAutoloader()->__invoke($proxyClassName);
        }

        $reflectionProperties                   = $reflection->getProperties();
        $this->hydratorClassNames[$className]   = $proxyClassName;
        $this->reflectionProperties[$className] = $reflectionProperties;

        return new $proxyClassName($reflectionProperties);
    }
}
