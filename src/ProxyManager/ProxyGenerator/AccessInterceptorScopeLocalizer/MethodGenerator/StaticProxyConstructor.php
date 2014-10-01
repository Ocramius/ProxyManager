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

namespace ProxyManager\ProxyGenerator\AccessInterceptorScopeLocalizer\MethodGenerator;

use ProxyManager\Exception\UnsupportedProxiedClassException;
use ProxyManager\Generator\MethodGenerator;
use ProxyManager\Generator\ParameterGenerator;
use ReflectionClass;
use Zend\Code\Generator\PropertyGenerator;

/**
 * The `staticProxyConstructor` implementation for an access interceptor scope localizer proxy
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
class StaticProxyConstructor extends MethodGenerator
{
    /**
     * Constructor
     *
     * @param ReflectionClass $originalClass
     */
    public function __construct(ReflectionClass $originalClass)
    {
        parent::__construct('staticProxyConstructor', array(), static::FLAG_PUBLIC | static::FLAG_STATIC);

        $localizedObject = new ParameterGenerator('localizedObject');
        $prefix          = new ParameterGenerator('prefixInterceptors');
        $suffix          = new ParameterGenerator('suffixInterceptors');

        $localizedObject->setType($originalClass->getName());
        $prefix->setDefaultValue(array());
        $suffix->setDefaultValue(array());
        $prefix->setType('array');
        $suffix->setType('array');

        $this->setParameter($localizedObject);
        $this->setParameter($prefix);
        $this->setParameter($suffix);

        $this->setDocblock(
            "Constructor to setup interceptors\n\n"
            . "@param \\" . $originalClass->getName() . " \$localizedObject\n"
            . "@param \\Closure[] \$prefixInterceptors method interceptors to be used before method logic\n"
            . "@param \\Closure[] \$suffixInterceptors method interceptors to be used before method logic\n\n"
            . "@return self"
        );
        $this->setBody(
            '$instance = (new \ReflectionClass(get_class()))->newInstanceWithoutConstructor();' . "\n\n"
            . '$instance->bindProxyProperties($localizedObject, $prefixInterceptors, $suffixInterceptors);' . "\n\n"
            . 'return $instance;'
        );
    }
}
