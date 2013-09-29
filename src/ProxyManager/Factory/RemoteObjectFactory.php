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

use ProxyManager\Factory\RemoteObject\AdapterInterface;
use ProxyManager\ProxyGenerator\RemoteObjectGenerator;
use ProxyManager\Generator\ClassGenerator;
use ProxyManager\Proxy\Exception\RemoteObjectException;
use ReflectionClass;

/**
 * Factory responsible of producing remote proxy objects
 *
 * @author Vincent Blanchon <blanchon.vincent@gmail.com>
 * @license MIT
 */
class RemoteObjectFactory extends AbstractBaseFactory
{
    /**
     * @param string            $interfaceName   
     * @param AdapterInterface  $adapter   
     * 
     * @return \ProxyManager\Proxy\RemoteObjectInterface
     */
    public function createProxy($interfaceName, AdapterInterface $adapter)
    {
        if (! isset($this->generatedClasses[$interfaceName])) {
            $this->generatedClasses[$interfaceName] = $this->inflector->getProxyClassName(
                $interfaceName,
                array('factory' => get_class($this))
            );
        }

        $proxyClassName = $this->generatedClasses[$interfaceName];

        if (! class_exists($proxyClassName)) {
            $interfaceReflection = new ReflectionClass($interfaceName);
            if (! $interfaceReflection->isInterface()) {
                throw new RemoteObjectException('Wrapped remote services must be an interface');
            }
            
            $interfaceName = $this->inflector->getUserClassName($interfaceName);
            $phpClass      = new ClassGenerator($proxyClassName);
            $generator     = new RemoteObjectGenerator();

            $generator->generate($interfaceReflection, $phpClass);
            $this->configuration->getGeneratorStrategy()->generate($phpClass);
            $this->configuration->getProxyAutoloader()->__invoke($proxyClassName);
        }

        return new $proxyClassName($adapter);
    }
}
