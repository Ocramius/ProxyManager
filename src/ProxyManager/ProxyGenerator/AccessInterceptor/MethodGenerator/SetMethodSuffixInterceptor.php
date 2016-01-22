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

declare(strict_types=1);

namespace ProxyManager\ProxyGenerator\AccessInterceptor\MethodGenerator;

use Closure;
use ProxyManager\Generator\MethodGenerator;
use Zend\Code\Generator\ParameterGenerator;
use Zend\Code\Generator\PropertyGenerator;

/**
 * Implementation for {@see \ProxyManager\Proxy\AccessInterceptorInterface::setMethodSuffixInterceptor}
 * for access interceptor objects
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
class SetMethodSuffixInterceptor extends MethodGenerator
{
    /**
     * Constructor
     *
     * @param PropertyGenerator $suffixInterceptor
     */
    public function __construct(PropertyGenerator $suffixInterceptor)
    {
        parent::__construct('setMethodSuffixInterceptor');

        $interceptor = new ParameterGenerator('suffixInterceptor');

        $interceptor->setType(Closure::class);
        $interceptor->setDefaultValue(null);
        $this->setParameter(new ParameterGenerator('methodName', 'string'));
        $this->setParameter($interceptor);
        $this->setDocblock('{@inheritDoc}');
        $this->setBody('$this->' . $suffixInterceptor->getName() . '[$methodName] = $suffixInterceptor;');
    }
}
