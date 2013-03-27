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

namespace ProxyManagerTestAsset;

/**
 * Base test class to catch instantiations of access interceptor value holders
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
class AccessInterceptorValueHolderMock
{
    /**
     * @var mixed
     */
    public $instance;

    /**
     * @var mixed
     */
    public $prefixInterceptors;

    /**
     * @var mixed
     */
    public $suffixInterceptors;

    /**
     * @param mixed $instance
     * @param mixed $prefixInterceptors
     * @param mixed $suffixInterceptors
     */
    public function __construct($instance, $prefixInterceptors, $suffixInterceptors)
    {
        $this->instance           = $instance;
        $this->prefixInterceptors = $prefixInterceptors;
        $this->suffixInterceptors = $suffixInterceptors;
    }
}
