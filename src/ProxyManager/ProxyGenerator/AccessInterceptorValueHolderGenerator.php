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

use CG\Generator\PhpClass;
use CG\Generator\PhpProperty;
use CG\Proxy\GeneratorInterface;

use ProxyManager\ProxyGenerator\AccessInterceptor\PhpMethod\MagicWakeup;
use ProxyManager\ProxyGenerator\AccessInterceptor\PhpMethod\SetMethodPrefixInterceptor;
use ProxyManager\ProxyGenerator\AccessInterceptor\PhpMethod\SetMethodSuffixInterceptor;
use ProxyManager\ProxyGenerator\AccessInterceptor\PhpProperty\MethodPrefixInterceptors;

use ProxyManager\ProxyGenerator\AccessInterceptorValueHolder\PhpMethod\Constructor;
use ProxyManager\ProxyGenerator\AccessInterceptorValueHolder\PhpMethod\InterceptedMethod;
use ProxyManager\ProxyGenerator\AccessInterceptorValueHolder\PhpMethod\MagicClone;
use ProxyManager\ProxyGenerator\AccessInterceptorValueHolder\PhpMethod\MagicGet;
use ProxyManager\ProxyGenerator\AccessInterceptorValueHolder\PhpMethod\MagicSet;
use ProxyManager\ProxyGenerator\AccessInterceptorValueHolder\PhpMethod\MagicUnset;

use ProxyManager\ProxyGenerator\ValueHolder\PhpMethod\GetWrappedValueHolderValue;

use ProxyManager\ProxyGenerator\LazyLoadingValueHolder\PhpProperty\InitializerProperty;
use ProxyManager\ProxyGenerator\LazyLoadingValueHolder\PhpProperty\ValueHolderProperty;

use ProxyManager\ProxyGenerator\ValueHolder\PhpMethod\MagicSleep;
use ReflectionClass;
use ReflectionMethod;

/**
 * Generator for proxies implementing {@see \ProxyManager\Proxy\ValueHolderInterface}
 * and {@see \ProxyManager\Proxy\AccessInterceptorInterface}
 *
 * {@inheritDoc}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
class AccessInterceptorValueHolderGenerator implements GeneratorInterface
{
    /**
     * {@inheritDoc}
     */
    public function generate(ReflectionClass $originalClass, PhpClass $generated)
    {
        $generated->setParentClassName($originalClass->getName());
        $generated->setInterfaceNames(
            array('ProxyManager\\Proxy\\AccessInterceptorInterface', 'ProxyManager\\Proxy\\ValueHolderInterface')
        );
        $generated->setProperty($valueHolder = new ValueHolderProperty());
        $generated->setProperty($prefixInterceptors = new MethodPrefixInterceptors());
        $generated->setProperty($suffixInterceptors = new MethodPrefixInterceptors());

        $excluded = array(
            '__get'    => true,
            '__set'    => true,
            '__isset'  => true,
            '__unset'  => true,
            '__clone'  => true,
            '__sleep'  => true,
            '__wakeup' => true,
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

        foreach ($methods as $method) {
            $generated->setMethod(
                InterceptedMethod::generateMethod($method, $valueHolder, $prefixInterceptors, $suffixInterceptors)
            );
        }

        $generated->setMethod(new Constructor($originalClass, $valueHolder, $prefixInterceptors, $suffixInterceptors));
        $generated->setMethod(new GetWrappedValueHolderValue($valueHolder));
        $generated->setMethod(new SetMethodPrefixInterceptor($prefixInterceptors));
        $generated->setMethod(new SetMethodSuffixInterceptor($suffixInterceptors));
        $generated->setMethod(new MagicSet($originalClass, $valueHolder, $prefixInterceptors, $suffixInterceptors));
        $generated->setMethod(new MagicGet($originalClass, $valueHolder, $prefixInterceptors, $suffixInterceptors));
        $generated->setMethod(new MagicUnset($originalClass, $valueHolder, $prefixInterceptors, $suffixInterceptors));
        $generated->setMethod(new MagicClone($originalClass, $valueHolder, $prefixInterceptors, $suffixInterceptors));
        $generated->setMethod(new MagicSleep($originalClass, $valueHolder));
        $generated->setMethod(new MagicWakeup($originalClass, $valueHolder));
    }
}
