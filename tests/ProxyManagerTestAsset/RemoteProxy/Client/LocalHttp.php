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

namespace ProxyManagerTestAsset\RemoteProxy\Client;

use Zend\Http\Client as HttpClient;
use ProxyManagerTestAsset\RemoteProxy\Request\XmlRpcLocal;

/**
 * Local client
 *
 * @author Vincent Blanchon <blanchon.vincent@gmail.com>
 * @license MIT
 */
class LocalHttp extends HttpClient
{
    private $uri;
    private $type;
    
    public function __construct($uri = null, $type = null)
    {
        if (!file_exists($uri)) {
            throw new \InvalidArgumentException(sprintf('Target file "%s" do not exists', $uri));
        }
        
        $this->uri = $uri;
        $this->type = $type;
    }
    
    /**
     * Send HTTP request
     *
     * @param  Request $request
     * @return Response
     * @throws Exception\RuntimeException
     * @throws Client\Exception\RuntimeException
     */
    public function send()
    {
        // body
        $body = $this->prepareBody();
        
        switch($this->type) {
            case 'json-rpc':
                $request = new \Zend\Json\Server\Request\Http();
                $request->loadJson($body);
                break;
            case 'xml-rpc':
                $request = new XmlRpcLocal();
                $request->loadXml($body);
                break;
            case 'soap':
                $request = $body;
                break;
        }
        ob_start();
        require $this->uri;
        $content = ob_get_clean();
       
        $response = new \Zend\Http\Response();
        $response->setStatusCode(200);
        $response->setContent($content);
        
        return $response;
    }
}
