This is a list of backwards compatibility (BC) breaks introduced in ProxyManager:

# 0.5.0

 * The Generated Hydrator has been removed - it is now available as a separate project
   at [Ocramius/GeneratedHydrator](https://github.com/Ocramius/GeneratedHydrator) [#65](https://github.com/Ocramius/ProxyManager/pull/65)
 * When having a `public function __get($name)` defined (by-val) and public properties, it won't be possible to get public
   properties by-ref while initializing the object. Either drop `__get()` or implement
   a by-ref `& __get()` [#126](https://github.com/Ocramius/ProxyManager/pull/126)
 * Proxies are now being always auto-generated if they could not be autoloaded by a factory. The methods
   [`ProxyManager\Configuration#setAutoGenerateProxies()`](https://github.com/Ocramius/ProxyManager/blob/0.5.0-BETA2/src/ProxyManager/Configuration.php#L67)
   and [`ProxyManager\Configuration#doesAutoGenerateProxies()`](https://github.com/Ocramius/ProxyManager/blob/0.5.0-BETA2/src/ProxyManager/Configuration.php#L75)
   are now no-op and deprecated, and will be removed in the next minor
   version [#87](https://github.com/Ocramius/ProxyManager/pull/87) [#90](https://github.com/Ocramius/ProxyManager/pull/90)
 * Proxy public properties defaults are now set before initialization [#116](https://github.com/Ocramius/ProxyManager/pull/116) [#122](https://github.com/Ocramius/ProxyManager/pull/122)

# 0.4.0

 * An optional parameter `$options` was introduced
   in [`ProxyManager\Inflector\ClassNameInflectorInterface#getProxyClassName($className, array $options = array())`](https://github.com/Ocramius/ProxyManager/blob/master/src/ProxyManager/Inflector/ClassNameInflectorInterface.php)
   parametrize the generated class name as of [#10](https://github.com/Ocramius/ProxyManager/pull/10)
   and [#59](https://github.com/Ocramius/ProxyManager/pull/59)
 * Generated hydrators no longer have constructor arguments. Any required reflection instantiation is now dealt with
   in the hydrator internally as of [#63](https://github.com/Ocramius/ProxyManager/pull/63)

# 0.3.4

 * Interface names are also supported for proxy generation as of [#40](https://github.com/Ocramius/ProxyManager/pull/40)

# 0.3.3

 * [Generated hydrators](https://github.com/Ocramius/ProxyManager/tree/master/docs/generated-hydrator.md) were introduced

# 0.3.2

 * An additional (optional) [by-ref parameter was added](https://github.com/Ocramius/ProxyManager/pull/31) 
   to the lazy loading proxies' initializer to allow unsetting the initializer with less overhead.

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
