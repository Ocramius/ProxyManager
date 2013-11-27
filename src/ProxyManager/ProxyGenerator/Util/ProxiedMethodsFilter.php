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

namespace ProxyManager\ProxyGenerator\Util;

use ReflectionClass;
use ReflectionMethod;

/**
 * Utility class used to filter methods that can be proxied
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
class ProxiedMethodsFilter
{
    /**
     * @param ReflectionClass $class    reflection class from which methods should be extracted
     * @param string[]        $excluded methods to be ignored
     *
     * @return ReflectionMethod[]
     */
    public static function getProxiedMethods(
        ReflectionClass $class,
        array $excluded = array('__get', '__set', '__isset', '__unset', '__clone', '__sleep', '__wakeup')
    ) {
        $ignored = array_flip(array_map('strtolower', $excluded));

        return array_filter(
            $class->getMethods(ReflectionMethod::IS_PUBLIC),
            function (ReflectionMethod $method) use ($ignored) {
                return ! (
                    $method->isConstructor()
                    || isset($ignored[strtolower($method->getName())])
                    || $method->isFinal()
                    || $method->isStatic()
                );
            }
        );
    }
}
