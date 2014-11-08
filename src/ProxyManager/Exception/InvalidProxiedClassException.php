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

namespace ProxyManager\Exception;

use InvalidArgumentException;
use ReflectionClass;
use ReflectionMethod;

/**
 * Exception for invalid proxied classes
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
class InvalidProxiedClassException extends InvalidArgumentException implements ExceptionInterface
{
    /**
     * @param ReflectionClass $reflection
     *
     * @return self
     */
    public static function interfaceNotSupported(ReflectionClass $reflection)
    {
        return new self(sprintf('Provided interface "%s" cannot be proxied', $reflection->getName()));
    }

    /**
     * @param ReflectionClass $reflection
     *
     * @return self
     */
    public static function finalClassNotSupported(ReflectionClass $reflection)
    {
        return new self(sprintf('Provided class "%s" is final and cannot be proxied', $reflection->getName()));
    }

    /**
     * @param ReflectionClass $reflection
     *
     * @return self
     */
    public static function abstractProtectedMethodsNotSupported(ReflectionClass $reflection)
    {
        return new self(sprintf(
            'Provided class "%s" has following protected abstract methods, and therefore cannot be proxied:' . "\n%s",
            $reflection->getName(),
            implode(
                "\n",
                array_map(
                    function (ReflectionMethod $reflectionMethod) {
                        return $reflectionMethod->getDeclaringClass()->getName() . '::' . $reflectionMethod->getName();
                    },
                    array_filter(
                        $reflection->getMethods(),
                        function (ReflectionMethod $method) {
                            return $method->isAbstract() && $method->isProtected();
                        }
                    )
                )
            )
        ));
    }
}
