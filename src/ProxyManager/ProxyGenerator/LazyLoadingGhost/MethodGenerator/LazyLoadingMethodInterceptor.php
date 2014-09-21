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

namespace ProxyManager\ProxyGenerator\LazyLoadingGhost\MethodGenerator;

use ProxyManager\Generator\MethodGenerator;
use Zend\Code\Generator\MethodGenerator as ZendMethodGenerator;
use Zend\Code\Generator\PropertyGenerator;
use Zend\Code\Reflection\MethodReflection;

/**
 * Method decorator for lazy loading value holder objects
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
class LazyLoadingMethodInterceptor extends MethodGenerator
{
    /**
     * @param \Zend\Code\Reflection\MethodReflection $originalMethod
     * @param \Zend\Code\Generator\PropertyGenerator $initializerProperty
     * @param \Zend\Code\Generator\MethodGenerator   $callInitializer
     *
     * @return LazyLoadingMethodInterceptor|static
     */
    public static function generateMethod(
        MethodReflection $originalMethod,
        PropertyGenerator $initializerProperty,
        ZendMethodGenerator $callInitializer
    ) {
        /* @var $method self */
        $method            = static::fromReflection($originalMethod);
        $parameters        = $originalMethod->getParameters();
        $methodName        = $originalMethod->getName();
        $initializerParams = array();
        $forwardedParams   = array();

        foreach ($parameters as $parameter) {
            $parameterName       = $parameter->getName();
            $initializerParams[] = var_export($parameterName, true) . ' => $' . $parameterName;
            $forwardedParams[]   = '$' . $parameterName;
        }

        $method->setBody(
            '$this->' . $initializerProperty->getName()
            . ' && $this->' . $callInitializer->getName()
            . '(' . var_export($methodName, true)
            . ', array(' . implode(', ', $initializerParams) .  "));\n\n"
            . 'return parent::'
            . $methodName . '(' . implode(', ', $forwardedParams) . ');'
        );
        $method->setDocblock('{@inheritDoc}');

        return $method;
    }
}
