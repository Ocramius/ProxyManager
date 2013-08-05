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

namespace ProxyManager\ProxyGenerator\PropertyGenerator;

use ProxyManager\Generator\Util\UniqueIdentifierGenerator;
use Zend\Code\Generator\PropertyGenerator;
use ReflectionClass;
use ReflectionProperty;

/**
 * Map of public properties that exist in the class being proxied
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
class PublicPropertiesMap extends PropertyGenerator
{
    /**
     * @var bool[]
     */
    private $publicProperties = array();

    /**
     * @param \ReflectionClass $originalClass
     */
    public function __construct(ReflectionClass $originalClass)
    {
        parent::__construct(UniqueIdentifierGenerator::getIdentifier('publicProperties'));

        foreach ($originalClass->getProperties(ReflectionProperty::IS_PUBLIC) as $publicProperty) {
            $this->publicProperties[$publicProperty->getName()] = true;
        }

        $this->setDefaultValue($this->publicProperties);
        $this->setVisibility(self::VISIBILITY_PRIVATE);
        $this->setStatic(true);
        $this->setDocblock('@var bool[] map of public properties of the parent class');
    }

    /**
     * @return bool whether there are no public properties
     */
    public function isEmpty()
    {
        return empty($this->publicProperties);
    }
}
