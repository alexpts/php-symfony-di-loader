# Symfony DI component loader

[![Build Status](https://travis-ci.org/alexpts/php-symfony-di-loader.svg?branch=master)](https://travis-ci.org/alexpts/php-symfony-di-loader)
[![Test Coverage](https://codeclimate.com/github/alexpts/php-symfony-di-loader/badges/coverage.svg)](https://codeclimate.com/github/alexpts/php-symfony-di-loader/coverage)
[![Code Climate](https://codeclimate.com/github/alexpts/php-symfony-di-loader/badges/gpa.svg)](https://codeclimate.com/github/alexpts/php-symfony-di-loader)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/alexpts/php-symfony-di-loader/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/alexpts/php-symfony-di-loader/?branch=master)


Simple loader for symfony [DependencyInjection component]( https://symfony.com/doc/current/components/dependency_injection.html)

### Install
`composer require alexpts/php-symfony-di-loader`


### Demo
```php
<?php
use PTS\SymfonyDiLoader\LoaderContainer;
use PTS\SymfonyDiLoader\FactoryContainer;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Config\FileLocator;

$factory = new FactoryContainer(YamlFileLoader::class, new FileLocator);

$configs = [__DIR__ . '/container.yml'];
$fileCache = __DIR__ .'/../cache/container.cache.php';

$loader = new LoaderContainer($configs, $fileCache, $factory);
$container = $loader->getContainer();
```


Loader has auto detect config changes and auto rebuild container.
For disable auto check expired cache:

```php
$loader = new LoaderContainer($configs, $fileCache, $factory);
$loader->setCheckExpired(false);
$container = $loader->getContainer();
```

