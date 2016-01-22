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

namespace ProxyManager\ProxyGenerator\Assertion;

use BadMethodCallException;
use ProxyManager\Exception\InvalidProxiedClassException;
use ReflectionClass;
use ReflectionMethod;

/**
 * Assertion that verifies that a class can be proxied
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
final class CanProxyAssertion
{
    /**
     * Disabled constructor: not meant to be instantiated
     *
     * @throws BadMethodCallException
     */
    public function __construct()
    {
        throw new BadMethodCallException('Unsupported constructor.');
    }

    /**
     * @param ReflectionClass $originalClass
     * @param bool            $allowInterfaces
     *
     * @return void
     *
     * @throws InvalidProxiedClassException
     */
    public static function assertClassCanBeProxied(ReflectionClass $originalClass, bool $allowInterfaces = true)
    {
        self::isNotFinal($originalClass);
        self::hasNoAbstractProtectedMethods($originalClass);

        if (! $allowInterfaces) {
            self::isNotInterface($originalClass);
        }
    }

    /**
     * @param ReflectionClass $originalClass
     *
     * @return void
     *
     * @throws InvalidProxiedClassException
     */
    private static function isNotFinal(ReflectionClass $originalClass)
    {
        if ($originalClass->isFinal()) {
            throw InvalidProxiedClassException::finalClassNotSupported($originalClass);
        }
    }

    /**
     * @param ReflectionClass $originalClass
     *
     * @return void
     *
     * @throws InvalidProxiedClassException
     */
    private static function hasNoAbstractProtectedMethods(ReflectionClass $originalClass)
    {
        $protectedAbstract = array_filter(
            $originalClass->getMethods(),
            function (ReflectionMethod $method) : bool {
                return $method->isAbstract() && $method->isProtected();
            }
        );

        if ($protectedAbstract) {
            throw InvalidProxiedClassException::abstractProtectedMethodsNotSupported($originalClass);
        }
    }

    /**
     * @param ReflectionClass $originalClass
     *
     * @return void
     *
     * @throws InvalidProxiedClassException
     */
    private static function isNotInterface(ReflectionClass $originalClass)
    {
        if ($originalClass->isInterface()) {
            throw InvalidProxiedClassException::interfaceNotSupported($originalClass);
        }
    }
}
