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

use ProxyManager\ProxyGenerator\LazyLoading\MethodGenerator\Constructor;

use ProxyManager\ProxyGenerator\LazyLoadingGhost\MethodGenerator\CallInitializer;
use ProxyManager\ProxyGenerator\LazyLoadingGhost\MethodGenerator\GetProxyInitializer;
use ProxyManager\ProxyGenerator\LazyLoadingGhost\MethodGenerator\InitializeProxy;
use ProxyManager\ProxyGenerator\LazyLoadingGhost\MethodGenerator\IsProxyInitialized;
use ProxyManager\ProxyGenerator\LazyLoadingGhost\MethodGenerator\LazyLoadingMethodInterceptor;
use ProxyManager\ProxyGenerator\LazyLoadingGhost\MethodGenerator\MagicClone;
use ProxyManager\ProxyGenerator\LazyLoadingGhost\MethodGenerator\MagicGet;
use ProxyManager\ProxyGenerator\LazyLoadingGhost\MethodGenerator\MagicIsset;
use ProxyManager\ProxyGenerator\LazyLoadingGhost\MethodGenerator\MagicSet;
use ProxyManager\ProxyGenerator\LazyLoadingGhost\MethodGenerator\MagicSleep;
use ProxyManager\ProxyGenerator\LazyLoadingGhost\MethodGenerator\MagicUnset;
use ProxyManager\ProxyGenerator\LazyLoadingGhost\MethodGenerator\SetProxyInitializer;

use ProxyManager\ProxyGenerator\LazyLoadingGhost\PropertyGenerator\InitializationTracker;
use ProxyManager\ProxyGenerator\LazyLoadingGhost\PropertyGenerator\InitializerProperty;

use ProxyManager\ProxyGenerator\PropertyGenerator\PublicPropertiesDefaults;
use ProxyManager\ProxyGenerator\PropertyGenerator\PublicPropertiesMap;
use ProxyManager\ProxyGenerator\Util\ProxiedMethodsFilter;
use ReflectionClass;
use Zend\Code\Generator\ClassGenerator;
use Zend\Code\Reflection\MethodReflection;

/**
 * Generator for proxies implementing {@see \ProxyManager\Proxy\GhostObjectInterface}
 *
 * {@inheritDoc}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
class LazyLoadingGhostGenerator implements ProxyGeneratorInterface
{
    /**
     * {@inheritDoc}
     */
    public function generate(ReflectionClass $originalClass, ClassGenerator $classGenerator)
    {
        $interfaces               = array('ProxyManager\\Proxy\\GhostObjectInterface');
        $publicProperties         = new PublicPropertiesMap($originalClass);
        $publicPropertiesDefaults = new PublicPropertiesDefaults($originalClass);

        if ($originalClass->isInterface()) {
            $interfaces[] = $originalClass->getName();
        } else {
            $classGenerator->setExtendedClass($originalClass->getName());
        }

        $classGenerator->setImplementedInterfaces($interfaces);
        $classGenerator->addPropertyFromGenerator($initializer = new InitializerProperty());
        $classGenerator->addPropertyFromGenerator($initializationTracker = new InitializationTracker());
        $classGenerator->addPropertyFromGenerator($publicProperties);
        $classGenerator->addPropertyFromGenerator($publicPropertiesDefaults);

        $init = new CallInitializer($initializer, $publicPropertiesDefaults, $initializationTracker);

        $classGenerator->addMethodFromGenerator($init);

        foreach (ProxiedMethodsFilter::getProxiedMethods($originalClass) as $method) {
            $classGenerator->addMethodFromGenerator(
                LazyLoadingMethodInterceptor::generateMethod(
                    new MethodReflection($method->getDeclaringClass()->getName(), $method->getName()),
                    $initializer,
                    $init
                )
            );
        }

        $classGenerator->addMethodFromGenerator(new Constructor($originalClass, $initializer));

        $classGenerator->addMethodFromGenerator(new MagicGet($originalClass, $initializer, $init, $publicProperties));
        $classGenerator->addMethodFromGenerator(new MagicSet($originalClass, $initializer, $init, $publicProperties));
        $classGenerator->addMethodFromGenerator(new MagicIsset($originalClass, $initializer, $init, $publicProperties));
        $classGenerator->addMethodFromGenerator(new MagicUnset($originalClass, $initializer, $init, $publicProperties));
        $classGenerator->addMethodFromGenerator(new MagicClone($originalClass, $initializer, $init));
        $classGenerator->addMethodFromGenerator(new MagicSleep($originalClass, $initializer, $init));

        $classGenerator->addMethodFromGenerator(new SetProxyInitializer($initializer));
        $classGenerator->addMethodFromGenerator(new GetProxyInitializer($initializer));
        $classGenerator->addMethodFromGenerator(new InitializeProxy($initializer, $init));
        $classGenerator->addMethodFromGenerator(new IsProxyInitialized($initializer));
    }
}
