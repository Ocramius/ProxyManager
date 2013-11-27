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

namespace ProxyManager\ProxyGenerator;

use ProxyManager\ProxyGenerator\RemoteObject\MethodGenerator\RemoteObjectMethod;
use ProxyManager\ProxyGenerator\RemoteObject\PropertyGenerator\AdapterProperty;
use ProxyManager\ProxyGenerator\RemoteObject\MethodGenerator\Constructor;
use ProxyManager\ProxyGenerator\RemoteObject\MethodGenerator\MagicGet;
use ProxyManager\ProxyGenerator\RemoteObject\MethodGenerator\MagicSet;
use ProxyManager\ProxyGenerator\RemoteObject\MethodGenerator\MagicIsset;
use ProxyManager\ProxyGenerator\RemoteObject\MethodGenerator\MagicUnset;

use ProxyManager\ProxyGenerator\Util\ProxiedMethodsFilter;
use ReflectionClass;
use Zend\Code\Generator\ClassGenerator;
use Zend\Code\Reflection\MethodReflection;

/**
 * Generator for proxies implementing {@see \ProxyManager\Proxy\RemoteObjectInterface}
 *
 * {@inheritDoc}
 *
 * @author Vincent Blanchon <blanchon.vincent@gmail.com>
 * @license MIT
 */
class RemoteObjectGenerator implements ProxyGeneratorInterface
{
    /**
     * {@inheritDoc}
     */
    public function generate(ReflectionClass $originalClass, ClassGenerator $classGenerator)
    {
        $interfaces = array('ProxyManager\\Proxy\\RemoteObjectInterface');

        if ($originalClass->isInterface()) {
            $interfaces[] = $originalClass->getName();
        } else {
            $classGenerator->setExtendedClass($originalClass->getName());
        }

        $classGenerator->setImplementedInterfaces($interfaces);
        $classGenerator->addPropertyFromGenerator($adapter = new AdapterProperty());

        $methods = ProxiedMethodsFilter::getProxiedMethods(
            $originalClass,
            array('__get', '__set', '__isset', '__unset')
        );

        foreach ($methods as $method) {
            $classGenerator->addMethodFromGenerator(
                RemoteObjectMethod::generateMethod(
                    new MethodReflection(
                        $method->getDeclaringClass()->getName(),
                        $method->getName()
                    ),
                    $adapter,
                    $originalClass
                )
            );
        }

        $classGenerator->addMethodFromGenerator(new Constructor($originalClass, $adapter));
        $classGenerator->addMethodFromGenerator(new MagicGet($originalClass, $adapter));
        $classGenerator->addMethodFromGenerator(new MagicSet($originalClass, $adapter));
        $classGenerator->addMethodFromGenerator(new MagicIsset($originalClass, $adapter));
        $classGenerator->addMethodFromGenerator(new MagicUnset($originalClass, $adapter));
    }
}
