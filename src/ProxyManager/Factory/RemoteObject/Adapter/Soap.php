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

use Zend\Soap\Client;

/**
 * Remote Object SOAP adapter
 *
 * @author Vincent Blanchon <blanchon.vincent@gmail.com>
 * @license MIT
 */
class Soap extends BaseAdapter
{
    /**
     * Soap client
     * @var \Zend\Soap\Client 
     */
    protected $client;

    /**
     * Adapter construction
     * @throws RemoteObjectException
     */
    protected function init()
    {
        if(!$this->options['wsdl']) {
            throw new RemoteObjectException('Soap wsdl is required in the "wsdl" key options');
        }
        if(null === $this->client) {
            $this->client = new Client($this->options['wsdl']);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function call($wrappedClass, $method, array $params = array())
    {
        $this->init();
        if(isset($this->map[$method])) {
            $method = $this->map[$method];
        }
        return $this->client->call($method, $params);
    }
}