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

declare(strict_types=1);

namespace ProxyManager\ProxyGenerator\AccessInterceptorScopeLocalizer\MethodGenerator;

use ProxyManager\Generator\MethodGenerator;
use ProxyManager\ProxyGenerator\AccessInterceptorScopeLocalizer\MethodGenerator\Util\InterceptorGenerator;
use Zend\Code\Generator\PropertyGenerator;
use Zend\Code\Reflection\MethodReflection;

/**
 * Method with additional pre- and post- interceptor logic in the body
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
class InterceptedMethod extends MethodGenerator
{
    /**
     * @param \Zend\Code\Reflection\MethodReflection $originalMethod
     * @param \Zend\Code\Generator\PropertyGenerator $prefixInterceptors
     * @param \Zend\Code\Generator\PropertyGenerator $suffixInterceptors
     *
     * @return self
     */
    public static function generateMethod(
        MethodReflection $originalMethod,
        PropertyGenerator $prefixInterceptors,
        PropertyGenerator $suffixInterceptors
    ) : self {
        /* @var $method self */
        $method          = static::fromReflection($originalMethod);
        $forwardedParams = [];

        foreach ($originalMethod->getParameters() as $parameter) {
            $forwardedParams[]   = ($parameter->isVariadic() ? '...' : '') . '$' . $parameter->getName();
        }

        $method->setDocblock('{@inheritDoc}');
        $method->setBody(
            InterceptorGenerator::createInterceptedMethodBody(
                '$returnValue = parent::'
                . $originalMethod->getName() . '(' . implode(', ', $forwardedParams) . ');',
                $method,
                $prefixInterceptors,
                $suffixInterceptors
            )
        );

        return $method;
    }
}
