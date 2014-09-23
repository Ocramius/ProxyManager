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

namespace ProxyManager\Generator;

use ReflectionClass;

/**
 * Method generator for magic methods
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
class MagicMethodGenerator extends MethodGenerator
{
    /**
     * @param ReflectionClass $originalClass
     * @param string          $name
     * @param array           $parameters
     */
    public function __construct(ReflectionClass $originalClass, $name, array $parameters = array())
    {
        parent::__construct(
            $name,
            $parameters,
            static::FLAG_PUBLIC,
            null,
            $originalClass->hasMethod($name) ? '{@inheritDoc}' : null
        );

        $this->setReturnsReference(strtolower($name) === '__get');

        if ($originalClass->hasMethod($name)) {
            $this->setReturnsReference($originalClass->getMethod($name)->returnsReference());
        }
    }
}
