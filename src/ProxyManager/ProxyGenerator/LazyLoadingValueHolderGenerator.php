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

use ProxyManager\ProxyGenerator\PropertyGenerator\PublicPropertiesMap;
use ProxyManager\ProxyGenerator\Util\ProxiedMethodsFilter;
use ProxyManager\ProxyGenerator\ValueHolder\MethodGenerator\GetWrappedValueHolderValue;

use ProxyManager\ProxyGenerator\AccessInterceptor\MethodGenerator\MagicWakeup;

use ProxyManager\ProxyGenerator\LazyLoading\MethodGenerator\Constructor;

use ProxyManager\ProxyGenerator\LazyLoadingValueHolder\MethodGenerator\GetProxyInitializer;
use ProxyManager\ProxyGenerator\LazyLoadingValueHolder\MethodGenerator\InitializeProxy;
use ProxyManager\ProxyGenerator\LazyLoadingValueHolder\MethodGenerator\IsProxyInitialized;
use ProxyManager\ProxyGenerator\LazyLoadingValueHolder\MethodGenerator\LazyLoadingMethodInterceptor;
use ProxyManager\ProxyGenerator\LazyLoadingValueHolder\MethodGenerator\MagicClone;
use ProxyManager\ProxyGenerator\LazyLoadingValueHolder\MethodGenerator\MagicGet;
use ProxyManager\ProxyGenerator\LazyLoadingValueHolder\MethodGenerator\MagicIsset;
use ProxyManager\ProxyGenerator\LazyLoadingValueHolder\MethodGenerator\MagicSet;
use ProxyManager\ProxyGenerator\LazyLoadingValueHolder\MethodGenerator\MagicSleep;
use ProxyManager\ProxyGenerator\LazyLoadingValueHolder\MethodGenerator\MagicUnset;
use ProxyManager\ProxyGenerator\LazyLoadingValueHolder\MethodGenerator\SetProxyInitializer;

use ProxyManager\ProxyGenerator\LazyLoadingValueHolder\PropertyGenerator\InitializerProperty;
use ProxyManager\ProxyGenerator\LazyLoadingValueHolder\PropertyGenerator\ValueHolderProperty;

use ReflectionClass;
use Zend\Code\Generator\ClassGenerator;
use Zend\Code\Reflection\MethodReflection;

/**
 * Generator for proxies implementing {@see \ProxyManager\Proxy\VirtualProxyInterface}
 *
 * {@inheritDoc}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
class LazyLoadingValueHolderGenerator implements ProxyGeneratorInterface
{
    /**
     * {@inheritDoc}
     */
    public function generate(ReflectionClass $originalClass, ClassGenerator $classGenerator)
    {
        $interfaces          = array('ProxyManager\\Proxy\\VirtualProxyInterface');
        $publicProperties    = new PublicPropertiesMap($originalClass);

        if ($originalClass->isInterface()) {
            $interfaces[] = $originalClass->getName();
        } else {
            $classGenerator->setExtendedClass($originalClass->getName());
        }

        $classGenerator->setImplementedInterfaces($interfaces);
        $classGenerator->addPropertyFromGenerator($valueHolder = new ValueHolderProperty());
        $classGenerator->addPropertyFromGenerator($initializer = new InitializerProperty());
        $classGenerator->addPropertyFromGenerator($publicProperties);

        foreach (ProxiedMethodsFilter::getProxiedMethods($originalClass) as $method) {
            $classGenerator->addMethodFromGenerator(
                LazyLoadingMethodInterceptor::generateMethod(
                    new MethodReflection($method->getDeclaringClass()->getName(), $method->getName()),
                    $initializer,
                    $valueHolder
                )
            );
        }

        $classGenerator->addMethodFromGenerator(new Constructor($originalClass, $initializer));

        $classGenerator->addMethodFromGenerator(
            new MagicGet($originalClass, $initializer, $valueHolder, $publicProperties)
        );
        $classGenerator->addMethodFromGenerator(
            new MagicSet($originalClass, $initializer, $valueHolder, $publicProperties)
        );
        $classGenerator->addMethodFromGenerator(
            new MagicIsset($originalClass, $initializer, $valueHolder, $publicProperties)
        );
        $classGenerator->addMethodFromGenerator(
            new MagicUnset($originalClass, $initializer, $valueHolder, $publicProperties)
        );
        $classGenerator->addMethodFromGenerator(new MagicClone($originalClass, $initializer, $valueHolder));
        $classGenerator->addMethodFromGenerator(new MagicSleep($originalClass, $initializer, $valueHolder));
        $classGenerator->addMethodFromGenerator(new MagicWakeup($originalClass));

        $classGenerator->addMethodFromGenerator(new SetProxyInitializer($initializer));
        $classGenerator->addMethodFromGenerator(new GetProxyInitializer($initializer));
        $classGenerator->addMethodFromGenerator(new InitializeProxy($initializer, $valueHolder));
        $classGenerator->addMethodFromGenerator(new IsProxyInitialized($valueHolder));

        $classGenerator->addMethodFromGenerator(new GetWrappedValueHolderValue($valueHolder));
    }
}
