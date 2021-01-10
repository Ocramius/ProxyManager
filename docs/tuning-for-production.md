# Tuning the ProxyManager for production

By default, all proxy factories generate the required proxy classes at runtime.

Proxy generation causes I/O operations and uses significant amounts of reflection, so be sure to have generated all of your 
proxies **before deploying your code on a live system**, or you may experience poor performance.

To generate proxies and store them as files, you need to use the `FileWriterGeneratorStrategy` by configuring ProxyManager. 
The files generated in the directory will be needed to autoload the proxies.

You can configure ProxyManager so that it will try autoloading the proxies first. Generating them en-masse is not yet 
implemented:

```php
    
$config = new \ProxyManager\Configuration();

// generate the proxies and store them as files
$fileLocator = new \ProxyManager\FileLocator\FileLocator(__DIR__.'/my/generated/classes/cache/dir');
$config->setGeneratorStrategy(new \ProxyManager\GeneratorStrategy\FileWriterGeneratorStrategy($fileLocator));

// set the directory to read the generated proxies from
$config->setProxiesTargetDir(__DIR__ . '/my/generated/classes/cache/dir');

// then register the autoloader
spl_autoload_register($config->getProxyAutoloader());

// pass the configuration to proxymanager factory
$factory = new ProxyManager\Factory\LazyLoadingValueHolderFactory($config);

```
You can also generate a classmap with all your proxy classes in it.

Please note that all the currently implemented `ProxyManager\Factory\*` classes accept a `ProxyManager\Configuration` object 
as an optional constructor parameter. This allows for fine-tuning of ProxyManager according to your needs.
