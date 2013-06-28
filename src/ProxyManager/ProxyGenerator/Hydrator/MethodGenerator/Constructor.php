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
use ReflectionClass;
use Zend\Code\Generator\PropertyGenerator;

/**
 * Method generator for the constructor of a hydrator proxy
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
class Constructor extends MethodGenerator
{
    /**
     * @param \ReflectionClass                                                           $originalClass
     * @param \ProxyManager\ProxyGenerator\Hydrator\PropertyGenerator\PropertyAccessor[] $propertyAccessors
     */
    public function __construct(ReflectionClass $originalClass, array $propertyAccessors)
    {
        parent::__construct('__construct');

        $this->setDocblock($originalClass->hasMethod('__construct') ? '{@inheritDoc}' : 'Constructor.');

        if (! empty($propertyAccessors)) {
            $this->setBody($this->getPropertyAccessorsInitialization($originalClass, $propertyAccessors));
        }
    }

    /**
     * Generates access interceptors initialization code
     *
     * @param \ReflectionClass                                                           $originalClass
     * @param \ProxyManager\ProxyGenerator\Hydrator\PropertyGenerator\PropertyAccessor[] $propertyAccessors
     *
     * @return string
     */
    private function getPropertyAccessorsInitialization(ReflectionClass $originalClass, array $propertyAccessors)
    {
        $reflectionInit   = '$reflectionClass = new \ReflectionClass('
            . var_export($originalClass->getName(), true) . ');';
        $propertiesInit   = '';
        $propertiesAccess = '';

        foreach ($propertyAccessors as $propertyAccessor) {
            $accessorName = $propertyAccessor->getName();

            $propertiesInit .= '$this->' . $accessorName . ' = $reflectionClass->getProperty('
                . var_export($propertyAccessor->getOriginalProperty()->getName(), true) . ");\n";
            $propertiesAccess .= '$this->' . $propertyAccessor->getName() . "->setAccessible(true);\n";
        }

        return $reflectionInit . "\n\n" . $propertiesInit . "\n" . $propertiesAccess;
    }
}
