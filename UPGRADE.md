This is a list of backwards compatibility (BC) breaks introduced in ProxyManager:

# 0.3.0

 * Dependency to [jms/cg](https://github.com/schmittjoh/cg-library) removed
 * Moved code generation logic to [`Zend\Code`](https://github.com/zendframework/zf2)
 * Added method [`ProxyManager\Inflector\ClassNameInflectorInterface#isProxyClassName($className)`](https://github.com/Ocramius/ProxyManager/blob/master/src/ProxyManager/Inflector/ClassNameInflectorInterface.php)
 * The constructor of [`ProxyManager\Autoloader\Autoloader`](https://github.com/Ocramius/ProxyManager/blob/master/src/ProxyManager/Autoloader/Autoloader.php)
   changed from `__construct(\ProxyManager\FileLocator\FileLocatorInterface $fileLocator)` to
   `__construct(\ProxyManager\FileLocator\FileLocatorInterface $fileLocator, \ProxyManager\Inflector\ClassNameInflectorInterface $classNameInflector)`
 * Classes implementing `CG\Core\GeneratorStrategyInterface` now implement
   [`ProxyManager\GeneratorStrategy\GeneratorStrategyInterface`](https://github.com/Ocramius/ProxyManager/blob/master/src/ProxyManager/GeneratorStrategy/GeneratorStrategyInterface.php)
   instead
 * All code generation logic has been replaced - If you wrote any logic based on `ProxyManager\ProxyGenerator`, you will
   have to rewrite it

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
