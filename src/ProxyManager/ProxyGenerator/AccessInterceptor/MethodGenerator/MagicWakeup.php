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

namespace ProxyManager\ProxyGenerator\AccessInterceptor\MethodGenerator;

use ProxyManager\Generator\MagicMethodGenerator;
use ReflectionClass;
use ReflectionProperty;

/**
 * Magic `__wakeup` for lazy loading value holder objects
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
class MagicWakeup extends MagicMethodGenerator
{
    /**
     * Constructor
     */
    public function __construct(ReflectionClass $originalClass)
    {
        parent::__construct($originalClass, '__wakeup');

        /* @var $publicProperties \ReflectionProperty[] */
        $publicProperties = $originalClass->getProperties(ReflectionProperty::IS_PUBLIC);
        $unsetProperties  = array();

        foreach ($publicProperties as $publicProperty) {
            $unsetProperties[] = '$this->' . $publicProperty->getName();
        }

        $this->setBody($unsetProperties ? 'unset(' . implode(', ', $unsetProperties) . ");" : '');
    }
}
