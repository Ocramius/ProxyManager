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
use ProxyManager\ProxyGenerator\HydratorGenerator;
use ReflectionClass;

/**
 * Factory responsible of producing hydrator proxies
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
class HydratorFactory extends AbstractBaseFactory
{
    /**
     * Cached proxy class names
     *
     * @var \Zend\Stdlib\Hydrator\HydratorInterface[]
     */
    private $hydrators = array();

    /**
     * @param string $className
     *
     * @return \Zend\Stdlib\Hydrator\HydratorInterface
     */
    public function createProxy($className)
    {
        if (isset($this->hydrators[$className])) {
            return $this->hydrators[$className];
        }

        $realClassName  = $this->inflector->getUserClassName($className);
        $proxyClassName = $this->inflector->getProxyClassName($realClassName, array('factory' => get_class($this)));

        if ($this->autoGenerate && ! class_exists($proxyClassName)) {
            $classGenerator = new ClassGenerator($proxyClassName);
            $generator      = new HydratorGenerator();

            $generator->generate(new ReflectionClass($realClassName), $classGenerator);
            $this->configuration->getGeneratorStrategy()->generate($classGenerator);
            $this->configuration->getProxyAutoloader()->__invoke($proxyClassName);
        }

        return $this->hydrators[$className] = new $proxyClassName();
    }
}
