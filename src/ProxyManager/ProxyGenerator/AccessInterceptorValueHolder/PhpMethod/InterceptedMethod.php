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

namespace ProxyManager\ProxyGenerator\AccessInterceptorValueHolder\PhpMethod;

use CG\Generator\PhpMethod;
use CG\Generator\PhpParameter;
use CG\Generator\PhpProperty;
use ProxyManager\ProxyGenerator\AccessInterceptorValueHolder\PhpMethod\Util\InterceptorGenerator;
use ReflectionMethod;

/**
 * Method with additional pre- and post- interceptor logic in the body
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
class InterceptedMethod extends PhpMethod
{
    /**
     * ReflectionClass $originalClass,
     * PhpProperty $valueHolderProperty,
     * PhpProperty $prefixInterceptors,
     * PhpProperty $suffixInterceptors
     * @return InterceptedMethod|static
     */
    public static function generateMethod(
        ReflectionMethod $originalMethod,
        PhpProperty $valueHolderProperty,
        PhpProperty $prefixInterceptors,
        PhpProperty $suffixInterceptors
    ) {
        /* @var $method self */
        $method            = static::fromReflection($originalMethod);
        $forwardedParams   = array();

        /* @var $parameter \CG\Generator\PhpParameter */
        foreach ($originalMethod->getParameters() as $parameter) {
            $forwardedParams[]   = '$' . $parameter->getName();
        }

        $method->setDocblock("/**\n * {@inheritDoc}\n */\n");
        $method->setBody(
            InterceptorGenerator::createInterceptedMethodBody(
                '$returnValue = $this->' . $valueHolderProperty->getName() . '->'
                . $originalMethod->getName() . '(' . implode(', ', $forwardedParams) . ');',
                $method,
                $valueHolderProperty,
                $prefixInterceptors,
                $suffixInterceptors
            )
        );

        return $method;
    }
}
