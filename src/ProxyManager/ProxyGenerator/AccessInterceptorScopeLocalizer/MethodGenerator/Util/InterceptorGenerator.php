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

namespace ProxyManager\ProxyGenerator\AccessInterceptorScopeLocalizer\MethodGenerator\Util;

use ProxyManager\Generator\MethodGenerator;
use Zend\Code\Generator\PropertyGenerator;

/**
 * Utility to create pre- and post- method interceptors around a given method body
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 *
 * @private - this class is just here as a small utility for this component, don't use it in your own code
 */
class InterceptorGenerator
{
    /**
     * @param string                                  $methodBody         the body of the previously generated code.
     *                                                                    It MUST assign the return value to a variable
     *                                                                    `$returnValue` instead of directly returning
     * @param \ProxyManager\Generator\MethodGenerator $method
     * @param \Zend\Code\Generator\PropertyGenerator  $prefixInterceptors
     * @param \Zend\Code\Generator\PropertyGenerator  $suffixInterceptors
     *
     * @return string
     */
    public static function createInterceptedMethodBody(
        string $methodBody,
        MethodGenerator $method,
        PropertyGenerator $prefixInterceptors,
        PropertyGenerator $suffixInterceptors,
        \ReflectionMethod $originalMethod = null
    ) : string {
        $name               = var_export($method->getName(), true);
        $prefixInterceptors = $prefixInterceptors->getName();
        $suffixInterceptors = $suffixInterceptors->getName();
        $params             = [];

        foreach ($method->getParameters() as $parameter) {
            $parameterName = $parameter->getName();
            $params[]      = var_export($parameterName, true) . ' => $' . $parameter->getName();
        }

        $paramsString = 'array(' . implode(', ', $params) . ')';

        return "if (isset(\$this->$prefixInterceptors" . "[$name])) {\n"
            . "    \$returnEarly       = false;\n"
            . "    \$prefixReturnValue = \$this->$prefixInterceptors" . "[$name]->__invoke("
            . "\$this, \$this, $name, $paramsString, \$returnEarly);\n\n"
            . "    if (\$returnEarly) {\n"
            . '        ' . self::returnStatement('$prefixReturnValue', $originalMethod)
            . "    }\n"
            . "}\n\n"
            . $methodBody . "\n\n"
            . "if (isset(\$this->$suffixInterceptors" . "[$name])) {\n"
            . "    \$returnEarly       = false;\n"
            . "    \$suffixReturnValue = \$this->$suffixInterceptors" . "[$name]->__invoke("
            . "\$this, \$this, $name, $paramsString, \$returnValue, \$returnEarly);\n\n"
            . "    if (\$returnEarly) {\n"
            . '        ' . self::returnStatement('$suffixReturnValue', $originalMethod)
            . "    }\n"
            . "}\n\n"
            . rtrim(self::returnStatement('$returnValue', $originalMethod));
    }

    private static function returnStatement(string $returnedValue, \ReflectionMethod $originalMethod = null) : string
    {
        $returnType = $originalMethod ? $originalMethod->getReturnType() : null;

        if ($returnType && 'void' === (string) $returnType) {
            return $returnedValue . ";\n\nreturn;\n";
        }

        return 'return ' . $returnedValue . ";\n";
    }
}
