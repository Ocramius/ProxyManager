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

namespace ProxyManager\ProxyGenerator\LazyLoadingValueHolder\MethodGenerator;

use ProxyManager\Generator\MagicMethodGenerator;
use ReflectionClass;
use Zend\Code\Generator\PropertyGenerator;

/**
 * Magic `__sleep` for lazy loading value holder objects
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
class MagicSleep extends MagicMethodGenerator
{
    /**
     * Constructor
     */
    public function __construct(
        ReflectionClass $originalClass,
        PropertyGenerator $initializerProperty,
        PropertyGenerator $valueHolderProperty
    ) {
        parent::__construct($originalClass, '__sleep');

        $initializer = $initializerProperty->getName();
        $valueHolder = $valueHolderProperty->getName();

        $this->setBody(
            '$this->' . $initializer . ' && $this->' . $initializer
            . '->__invoke($this->' . $valueHolder . ', $this, \'__sleep\', array(), $this->'
            . $initializer . ');' . "\n\n"
            . 'return array(' . var_export($valueHolder, true) . ');'
        );
    }
}
