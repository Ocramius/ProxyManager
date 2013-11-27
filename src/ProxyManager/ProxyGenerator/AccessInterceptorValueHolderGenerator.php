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

use ProxyManager\ProxyGenerator\AccessInterceptor\MethodGenerator\MagicWakeup;
use ProxyManager\ProxyGenerator\AccessInterceptor\MethodGenerator\SetMethodPrefixInterceptor;
use ProxyManager\ProxyGenerator\AccessInterceptor\MethodGenerator\SetMethodSuffixInterceptor;
use ProxyManager\ProxyGenerator\AccessInterceptor\PropertyGenerator\MethodPrefixInterceptors;
use ProxyManager\ProxyGenerator\AccessInterceptor\PropertyGenerator\MethodSuffixInterceptors;

use ProxyManager\ProxyGenerator\AccessInterceptorValueHolder\MethodGenerator\Constructor;
use ProxyManager\ProxyGenerator\AccessInterceptorValueHolder\MethodGenerator\InterceptedMethod;
use ProxyManager\ProxyGenerator\AccessInterceptorValueHolder\MethodGenerator\MagicClone;
use ProxyManager\ProxyGenerator\AccessInterceptorValueHolder\MethodGenerator\MagicGet;
use ProxyManager\ProxyGenerator\AccessInterceptorValueHolder\MethodGenerator\MagicIsset;
use ProxyManager\ProxyGenerator\AccessInterceptorValueHolder\MethodGenerator\MagicSet;
use ProxyManager\ProxyGenerator\AccessInterceptorValueHolder\MethodGenerator\MagicUnset;

use ProxyManager\ProxyGenerator\PropertyGenerator\PublicPropertiesMap;
use ProxyManager\ProxyGenerator\Util\ProxiedMethodsFilter;
use ProxyManager\ProxyGenerator\ValueHolder\MethodGenerator\GetWrappedValueHolderValue;

use ProxyManager\ProxyGenerator\LazyLoadingValueHolder\PropertyGenerator\ValueHolderProperty;

use ProxyManager\ProxyGenerator\ValueHolder\MethodGenerator\MagicSleep;
use ReflectionClass;
use Zend\Code\Generator\ClassGenerator;
use Zend\Code\Reflection\MethodReflection;

/**
 * Generator for proxies implementing {@see \ProxyManager\Proxy\ValueHolderInterface}
 * and {@see \ProxyManager\Proxy\AccessInterceptorInterface}
 *
 * {@inheritDoc}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
class AccessInterceptorValueHolderGenerator implements ProxyGeneratorInterface
{
    /**
     * {@inheritDoc}
     */
    public function generate(ReflectionClass $originalClass, ClassGenerator $classGenerator)
    {
        $publicProperties    = new PublicPropertiesMap($originalClass);
        $interfaces          = array(
            'ProxyManager\\Proxy\\AccessInterceptorInterface',
            'ProxyManager\\Proxy\\ValueHolderInterface',
        );

        if ($originalClass->isInterface()) {
            $interfaces[] = $originalClass->getName();
        } else {
            $classGenerator->setExtendedClass($originalClass->getName());
        }

        $classGenerator->setImplementedInterfaces($interfaces);
        $classGenerator->addPropertyFromGenerator($valueHolder = new ValueHolderProperty());
        $classGenerator->addPropertyFromGenerator($prefixInterceptors = new MethodPrefixInterceptors());
        $classGenerator->addPropertyFromGenerator($suffixInterceptors = new MethodSuffixInterceptors());
        $classGenerator->addPropertyFromGenerator($publicProperties);

        foreach (ProxiedMethodsFilter::getProxiedMethods($originalClass) as $method) {
            $classGenerator->addMethodFromGenerator(
                InterceptedMethod::generateMethod(
                    new MethodReflection($method->getDeclaringClass()->getName(), $method->getName()),
                    $valueHolder,
                    $prefixInterceptors,
                    $suffixInterceptors
                )
            );
        }

        $classGenerator->addMethodFromGenerator(
            new Constructor($originalClass, $valueHolder, $prefixInterceptors, $suffixInterceptors)
        );
        $classGenerator->addMethodFromGenerator(
            new GetWrappedValueHolderValue($valueHolder)
        );
        $classGenerator->addMethodFromGenerator(
            new SetMethodPrefixInterceptor($prefixInterceptors)
        );
        $classGenerator->addMethodFromGenerator(
            new SetMethodSuffixInterceptor($suffixInterceptors)
        );

        $classGenerator->addMethodFromGenerator(
            new MagicGet($originalClass, $valueHolder, $prefixInterceptors, $suffixInterceptors, $publicProperties)
        );

        $classGenerator->addMethodFromGenerator(
            new MagicSet($originalClass, $valueHolder, $prefixInterceptors, $suffixInterceptors, $publicProperties)
        );

        $classGenerator->addMethodFromGenerator(
            new MagicIsset($originalClass, $valueHolder, $prefixInterceptors, $suffixInterceptors, $publicProperties)
        );

        $classGenerator->addMethodFromGenerator(
            new MagicUnset($originalClass, $valueHolder, $prefixInterceptors, $suffixInterceptors, $publicProperties)
        );

        $classGenerator->addMethodFromGenerator(
            new MagicClone($originalClass, $valueHolder, $prefixInterceptors, $suffixInterceptors)
        );
        $classGenerator->addMethodFromGenerator(
            new MagicSleep($originalClass, $valueHolder)
        );
        $classGenerator->addMethodFromGenerator(
            new MagicWakeup($originalClass, $valueHolder)
        );
    }
}
