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
 * The `__construct` implementation for lazy loading proxies
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
class Constructor extends MethodGenerator
{
    /**
     * Constructor
     */
    public function __construct(
        ReflectionClass $originalClass,
        PropertyGenerator $prefixInterceptors,
        PropertyGenerator $suffixInterceptors
    ) {
        parent::__construct('__construct');

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

        $localizedProperties = array();

        foreach ($originalClass->getProperties() as $originalProperty) {
            if ((! method_exists('Closure', 'bind')) && $originalProperty->isPrivate()) {
                // @codeCoverageIgnoreStart
                throw UnsupportedProxiedClassException::unsupportedLocalizedReflectionProperty($originalProperty);
                // @codeCoverageIgnoreEnd
            }

            $propertyName = $originalProperty->getName();

            if ($originalProperty->isPrivate()) {
                $localizedProperties[] = "\\Closure::bind(function () use (\$localizedObject) {\n    "
                    . '$this->' . $propertyName . ' = & $localizedObject->' . $propertyName . ";\n"
                    . '}, $this, ' . var_export($originalProperty->getDeclaringClass()->getName(), true)
                    . ')->__invoke();';
            } else {
                $localizedProperties[] = '$this->' . $propertyName . ' = & $localizedObject->' . $propertyName . ";";
            }
        }

        $this->setDocblock(
            "@override constructor to setup interceptors\n\n"
            . "@param \\" . $originalClass->getName() . " \$localizedObject\n"
            . "@param \\Closure[] \$prefixInterceptors method interceptors to be used before method logic\n"
            . "@param \\Closure[] \$suffixInterceptors method interceptors to be used before method logic"
        );
        $this->setBody(
            (empty($localizedProperties) ? '' : implode("\n\n", $localizedProperties) . "\n\n")
            . '$this->' . $prefixInterceptors->getName() . " = \$prefixInterceptors;\n"
            . '$this->' . $suffixInterceptors->getName() . " = \$suffixInterceptors;"
        );
    }
}
