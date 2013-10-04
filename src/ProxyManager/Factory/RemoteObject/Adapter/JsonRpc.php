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

namespace ProxyManager\Factory\RemoteObject\Adapter;

use Zend\Json\Server\Client;
use ProxyManager\Proxy\Exception\RemoteObjectException;

/**
 * Remote Object JSON RPC adapter
 *
 * @author Vincent Blanchon <blanchon.vincent@gmail.com>
 * @license MIT
 */
class JsonRpc extends BaseAdapter
{
    /**
     * Rpc client building
     *
     * @param string $uri
     *
     * @throws \ProxyManager\Proxy\Exception\RemoteObjectException
     */
    public function __construct($uri)
    {
        if (! class_exists('Zend\Json\Server\Client')) {
            throw new RemoteObjectException('JsonRpc adapter does not exists. Please install zend-json package.');
        }

        if (empty($uri)) {
            throw new RemoteObjectException('Webservices URI is required');
        }

        parent::__construct($uri);
    }

    /**
     * {@inheritDoc}
     */
    protected function assemble($wrappedClass, $method)
    {
        return $wrappedClass . '.' . $method;
    }
    
    /**
     * {@inheritDoc}
     */
    public function getClient()
    {
        if (null === $this->client) {
            $this->setClient(new Client($this->uri->toString()));
        }
        return $this->client;
    }
}
