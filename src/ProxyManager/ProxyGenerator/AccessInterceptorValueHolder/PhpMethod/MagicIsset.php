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

namespace ProxyManager\ProxyGenerator\AccessInterceptorValueHolder\PhpMethod;

use ProxyManager\ProxyGenerator\AccessInterceptorValueHolder\PhpMethod\Util\InterceptorGenerator;
use ReflectionClass;
use CG\Generator\PhpMethod;
use CG\Generator\PhpParameter;
use CG\Generator\PhpProperty;

/**
 * Magic `__isset` for method interceptor value holder objects
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
class MagicIsset extends PhpMethod
{
    /**
     * Constructor
     */
    public function __construct(
        ReflectionClass $originalClass,
        PhpProperty $valueHolder,
        PhpProperty $prefixInterceptors,
        PhpProperty $suffixInterceptors
    ) {
        $inheritDoc  = $originalClass->hasMethod('__isset') ? "\n * {@inheritDoc}\n * " : '';

        parent::__construct('__isset');
        $this->setDocblock('/**' . $inheritDoc . "\n * @param string \$name\n */");
        $this->setParameters(array(new PhpParameter('name')));
        $this->setBody(
            InterceptorGenerator::createInterceptedMethodBody(
                '$returnValue = isset($this->' . $valueHolder->getName() . '->$name);',
                $this,
                $valueHolder,
                $prefixInterceptors,
                $suffixInterceptors
            )
        );
    }
}
