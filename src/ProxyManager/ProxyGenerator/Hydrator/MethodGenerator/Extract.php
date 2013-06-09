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

namespace ProxyManager\ProxyGenerator\Hydrator\MethodGenerator;

use ProxyManager\Generator\MethodGenerator;
use ProxyManager\Generator\ParameterGenerator;
use Zend\Code\Generator\PropertyGenerator;

/**
 * Method generator for the `extract` method of a hydrator proxy
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
class Extract extends MethodGenerator
{
    /**
     * @param \ReflectionProperty[]                                                      $accessibleProperties
     * @param \ProxyManager\ProxyGenerator\Hydrator\PropertyGenerator\PropertyAccessor[] $propertyAccessors
     */
    public function __construct(array $accessibleProperties, array $propertyAccessors)
    {
        parent::__construct('extract');
        $this->setDocblock("{@inheritDoc}");
        $this->setParameter(new ParameterGenerator('object'));

        if (empty($accessibleProperties) && empty($propertyAccessors)) {
            // no properties to hydrate
            $this->setBody('return array();');

            return;
        }

        $body = '';

        if (! empty($propertyAccessors)) {
            $body = "\$data = (array) \$object;\n\n";
        }

        $body .= 'return array(';

        foreach ($accessibleProperties as $accessibleProperty) {
            if (empty($propertyAccessors) || ! $accessibleProperty->isProtected()) {
                $body .= "\n    "
                    . var_export($accessibleProperty->getName(), true)
                    . ' => $object->' . $accessibleProperty->getName() . ',';
            } else {
                $body .= "\n    "
                    . var_export($accessibleProperty->getName(), true)
                    . ' => $data["\\0*\\0' . $accessibleProperty->getName() . '"],';
            }
        }

        foreach ($propertyAccessors as $propertyAccessor) {
            $body .= "\n    "
                . var_export($propertyAccessor->getOriginalProperty()->getName(), true)
                . ' => $data["'
                . '\\0' . $propertyAccessor->getOriginalProperty()->getDeclaringClass()->getName()
                . '\\0' . $propertyAccessor->getOriginalProperty()->getName()
                . '"],';
        }

        $body .= "\n);";

        $this->setBody($body);
    }
}
