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

use ProxyManager\Exception\InvalidProxiedClassException;

use ProxyManager\ProxyGenerator\AccessInterceptor\MethodGenerator\SetMethodPrefixInterceptor;
use ProxyManager\ProxyGenerator\AccessInterceptor\MethodGenerator\SetMethodSuffixInterceptor;
use ProxyManager\ProxyGenerator\AccessInterceptor\PropertyGenerator\MethodPrefixInterceptors;

use ProxyManager\ProxyGenerator\AccessInterceptorScopeLocalizer\MethodGenerator\Constructor;
use ProxyManager\ProxyGenerator\AccessInterceptorScopeLocalizer\MethodGenerator\InterceptedMethod;
use ProxyManager\ProxyGenerator\AccessInterceptorScopeLocalizer\MethodGenerator\MagicClone;
use ProxyManager\ProxyGenerator\AccessInterceptorScopeLocalizer\MethodGenerator\MagicGet;
use ProxyManager\ProxyGenerator\AccessInterceptorScopeLocalizer\MethodGenerator\MagicSet;
use ProxyManager\ProxyGenerator\AccessInterceptorScopeLocalizer\MethodGenerator\MagicIsset;

use ProxyManager\ProxyGenerator\AccessInterceptorScopeLocalizer\MethodGenerator\MagicSleep;
use ProxyManager\ProxyGenerator\AccessInterceptorScopeLocalizer\MethodGenerator\MagicUnset;
use ProxyManager\ProxyGenerator\PropertyGenerator\PublicPropertiesMap;

use ReflectionClass;
use ReflectionMethod;
use Zend\Code\Generator\ClassGenerator;
use Zend\Code\Reflection\MethodReflection;

/**
 * Generator for proxies implementing {@see \ProxyManager\Proxy\ValueHolderInterface}
 * and localizing scope of the proxied object at instantiation
 *
 * {@inheritDoc}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
class AccessInterceptorScopeLocalizerGenerator implements ProxyGeneratorInterface
{
    /**
     * {@inheritDoc}
     */
    public function generate(ReflectionClass $originalClass, ClassGenerator $classGenerator)
    {
        if ($originalClass->isInterface()) {
            throw InvalidProxiedClassException::interfaceNotSupported($originalClass);
        }

        $classGenerator->setExtendedClass($originalClass->getName());
        $classGenerator->setImplementedInterfaces(array('ProxyManager\\Proxy\\AccessInterceptorInterface'));
        $classGenerator->addPropertyFromGenerator($prefixInterceptors = new MethodPrefixInterceptors());
        $classGenerator->addPropertyFromGenerator($suffixInterceptors = new MethodPrefixInterceptors());

        $excluded = array(
            '__get'    => true,
            '__set'    => true,
            '__isset'  => true,
            '__unset'  => true,
            '__clone'  => true,
            '__sleep'  => true,
        );

        $methods = array_filter(
            $originalClass->getMethods(ReflectionMethod::IS_PUBLIC),
            function (ReflectionMethod $method) use ($excluded) {
                return ! (
                    $method->isConstructor()
                    || isset($excluded[strtolower($method->getName())])
                    || $method->isFinal()
                    || $method->isStatic()
                );
            }
        );

        /* @var $methods \ReflectionMethod[] */
        foreach ($methods as $method) {
            $classGenerator->addMethodFromGenerator(
                InterceptedMethod::generateMethod(
                    new MethodReflection($method->getDeclaringClass()->getName(), $method->getName()),
                    $prefixInterceptors,
                    $suffixInterceptors
                )
            );
        }

        $classGenerator->addMethodFromGenerator(
            new Constructor($originalClass, $prefixInterceptors, $suffixInterceptors)
        );
        $classGenerator->addMethodFromGenerator(new SetMethodPrefixInterceptor($prefixInterceptors));
        $classGenerator->addMethodFromGenerator(new SetMethodSuffixInterceptor($suffixInterceptors));
        $classGenerator->addMethodFromGenerator(
            new MagicGet($originalClass, $prefixInterceptors, $suffixInterceptors)
        );
        $classGenerator->addMethodFromGenerator(
            new MagicSet($originalClass, $prefixInterceptors, $suffixInterceptors)
        );
        $classGenerator->addMethodFromGenerator(
            new MagicIsset($originalClass, $prefixInterceptors, $suffixInterceptors)
        );
        $classGenerator->addMethodFromGenerator(
            new MagicUnset($originalClass, $prefixInterceptors, $suffixInterceptors)
        );
        $classGenerator->addMethodFromGenerator(
            new MagicSleep($originalClass, $prefixInterceptors, $suffixInterceptors)
        );
        $classGenerator->addMethodFromGenerator(
            new MagicClone($originalClass, $prefixInterceptors, $suffixInterceptors)
        );
    }
}
