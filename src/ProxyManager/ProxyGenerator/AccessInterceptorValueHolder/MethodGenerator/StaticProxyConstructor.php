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

namespace ProxyManager\ProxyGenerator\AccessInterceptorValueHolder\MethodGenerator;

use ReflectionClass;
use ReflectionProperty;
use ProxyManager\Generator\MethodGenerator;
use ProxyManager\Generator\ParameterGenerator;
use Zend\Code\Generator\PropertyGenerator;

/**
 * The `staticProxyConstructor` implementation for access interceptor value holders
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
class StaticProxyConstructor extends MethodGenerator
{
    /**
     * Constructor
     */
    public function __construct(
        ReflectionClass $originalClass,
        PropertyGenerator $valueHolder,
        PropertyGenerator $prefixInterceptors,
        PropertyGenerator $suffixInterceptors
    ) {
        parent::__construct('staticProxyConstructor', array(), static::FLAG_PUBLIC | static::FLAG_STATIC);

        $prefix = new ParameterGenerator('prefixInterceptors');
        $suffix = new ParameterGenerator('suffixInterceptors');

        $prefix->setDefaultValue(array());
        $suffix->setDefaultValue(array());
        $prefix->setType('array');
        $suffix->setType('array');

        $this->setParameter(new ParameterGenerator('wrappedObject'));
        $this->setParameter($prefix);
        $this->setParameter($suffix);

        /* @var $publicProperties \ReflectionProperty[] */
        $publicProperties  = $originalClass->getProperties(ReflectionProperty::IS_PUBLIC);
        $unsetProperties   = array();
        $instanceGenerator = '$instance = (new \ReflectionClass(get_class()))->newInstanceWithoutConstructor();'
            . "\n\n";

        foreach ($publicProperties as $publicProperty) {
            $unsetProperties[] = '$instance->' . $publicProperty->getName();
        }

        $this->setDocblock(
            "@override constructor to setup interceptors\n\n"
            . "@param \\" . $originalClass->getName() . " \$wrappedObject\n"
            . "@param \\Closure[] \$prefixInterceptors method interceptors to be used before method logic\n"
            . "@param \\Closure[] \$suffixInterceptors method interceptors to be used before method logic"
        );
        $this->setBody(
            $instanceGenerator,
            ($unsetProperties ? 'unset(' . implode(', ', $unsetProperties) . ");\n\n" : '')
            . '$instance->' . $valueHolder->getName() . " = \$wrappedObject;\n"
            . '$instance->' . $prefixInterceptors->getName() . " = \$prefixInterceptors;\n"
            . '$instance->' . $suffixInterceptors->getName() . " = \$suffixInterceptors;\n\n"
            . 'return $instance'
        );
    }
}
