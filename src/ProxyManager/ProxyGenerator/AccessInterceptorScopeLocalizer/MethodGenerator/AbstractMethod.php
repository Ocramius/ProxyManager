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

use ReflectionClass;
use Zend\Code\Generator\MethodGenerator;

/**
 * Create methods to be compliance with abstracts methods on
 * Proxied classes.
 *
 * @author Jefersson Nathan <malukenho@phpse.net>
 * @license MIT
 */
class AbstractMethod extends MethodGenerator
{
    /**
     * Constructor.
     *
     * @param ReflectionClass $originalClass
     * @param string          $name
     */
    public function __construct(ReflectionClass $originalClass, $name)
    {
        parent::__construct($name);
        $method = $originalClass->getMethod($name);

        foreach ($method->getParameters() as $param) {
            $this->setParameter($param->getName());
        }
    }

    /**
     * Return a collection of abstractMethods objects.
     *
     * @param ReflectionClass $originalClass
     * @param array           $methods
     *
     * @return AbstractMethod[]
     */
    public static function createCollection(ReflectionClass $originalClass, array $methods)
    {
        $methodCollection = array();
        foreach ($methods as $methodName) {
            $methodCollection[] = new self($originalClass, $methodName);
        }

        return $methodCollection;
    }
}
