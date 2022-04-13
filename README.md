# Symfony DI component loader

[![codecov](https://codecov.io/gh/alexpts/php-symfony-di-loader/branch/master/graph/badge.svg?token=14L6IJA5UE)](https://codecov.io/gh/alexpts/php-symfony-di-loader)

Simple loader for symfony [DependencyInjection component]( https://symfony.com/doc/current/components/dependency_injection.html)

### Install
`composer require alexpts/symfony-di-loader`


### Demo
```php
<?php
use PTS\SymfonyDiLoader\LoaderContainer;

$configs = ['./container.yml', './container.xml', './container.php'];
$fileCache = __DIR__ . '/var/container.cache.php';

$loader = new LoaderContainer;
$container = $loader->getContainer($configs, $fileCache);
```

Loader has autodetect config changes and watch services from configs and auto rebuild container.
You can disable all watch for production:
```php
$loader = new LoaderContainer;
$loader->setCheckExpired(false); // watch configs disable
$loader->getWatcher()->setIsWatchReflection(false); // watch service class disable
$container = $loader->getContainer($configs, $fileCache);
```

Custom container extension:
```php
$loader = new LoaderContainer;
$loader->addExtension($someContainerExtension);
$loader->addExtension($someContainerExtension2);
$container = $loader->getContainer($configs, $fileCache);
```
