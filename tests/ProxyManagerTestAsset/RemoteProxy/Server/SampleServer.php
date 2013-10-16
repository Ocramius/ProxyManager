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

namespace ProxyManagerTestAsset\RemoteProxy\Server;

/**
 * Server side mock
 *
 * @author Vincent Blanchon <blanchon.vincent@gmail.com>
 * @license MIT
 */
class SampleServer
{
    protected $alias = array(
        'ProxyManagerTestAsset\RemoteProxy\FooServiceInterface' => 'ProxyManagerTestAsset\RemoteProxy\Foo',
        'ProxyManagerTestAsset\RemoteProxy\BazServiceInterface' => 'ProxyManagerTestAsset\RemoteProxy\Foo',
    );
    
    /**
     * Dispatch request
     * @param strng $serviceName
     * @param array $params
     */
    public function dispatch($serviceName, array $params)
    {
        $infos = explode('.', $serviceName);
        $className = $infos[0];
        if (isset($this->alias[$className])) {
            $className = $this->alias[$className];
        }
        $class = new $className;
        return call_user_func_array(array($class, $infos[1]), $params);
    }
}
