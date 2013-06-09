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
 * Method generator for the `hydrate` method of a hydrator proxy
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
class Hydrate extends MethodGenerator
{
    /**
     * @param \ReflectionProperty[]                                                      $accessibleProperties
     * @param \ProxyManager\ProxyGenerator\Hydrator\PropertyGenerator\PropertyAccessor[] $propertyAccessors
     */
    public function __construct(array $accessibleProperties, array $propertyAccessors)
    {
        parent::__construct('hydrate');
        $this->setDocblock("{@inheritDoc}");
        $this->setParameter(new ParameterGenerator('data', 'array'));
        $this->setParameter(new ParameterGenerator('object'));

        $body = '';

        foreach ($accessibleProperties as $accessibleProperty) {
            $body .= '$object->'
                . $accessibleProperty->getName()
                . ' = $data['
                . var_export($accessibleProperty->getName(), true)
                . "];\n";
        }

        foreach ($propertyAccessors as $propertyAccessor) {
            $body .= '$this->'
                . $propertyAccessor->getName()
                . '->setValue($object, $data['
                . var_export($propertyAccessor->getOriginalProperty()->getName(), true)
                . "]);\n";
        }

        $body .= "\nreturn \$object;";

        $this->setBody($body);
    }
}
