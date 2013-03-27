This is a list of backwards compatibility (BC) breaks introduced in ProxyManager:

# 0.2.0

 * The signature of initializers to be used with proxies implementing
   [`ProxyManager\Proxy\LazyLoadingInterface`](https://github.com/Ocramius/ProxyManager/blob/master/src/ProxyManager/Proxy/LazyLoadingInterface.php)
   changed from:

   ```php
   $initializer = function ($proxy, & $wrappedObject, $method, $parameters) {};
   ```

   to

   ```php
   $initializer = function (& $wrappedObject, $proxy, $method, $parameters) {};
   ```

   Only the order of parameters passed to the closures has been changed.
