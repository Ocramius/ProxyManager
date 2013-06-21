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

namespace ProxyManager\ProxyGenerator\Hydrator\PropertyGenerator;

use ReflectionProperty;
use Zend\Code\Generator\PropertyGenerator;
use ProxyManager\Generator\Util\UniqueIdentifierGenerator;

/**
 * Property that contains a {@see \ReflectionProperty} that functions as an accessor
 * for inaccessible proxied object's properties.
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
class PropertyAccessor extends PropertyGenerator
{
    /**
     * @var \ReflectionProperty
     */
    protected $accessedProperty;

    /**
     * @param \ReflectionProperty $accessedProperty
     */
    public function __construct(ReflectionProperty $accessedProperty)
    {
        $this->accessedProperty = $accessedProperty;
        $originalName           = $this->accessedProperty->getName();

        parent::__construct(UniqueIdentifierGenerator::getIdentifier($originalName . 'Accessor'));
        $this->setVisibility(self::VISIBILITY_PRIVATE);
        $this->setDocblock("@var \\ReflectionProperty used to access {@see parent::$originalName}");
    }

    /**
     * @return \ReflectionProperty
     */
    public function getOriginalProperty()
    {
        return $this->accessedProperty;
    }
}
