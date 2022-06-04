# Symfony DI component loader

[![phpunit](https://github.com/alexpts/php-symfony-di-loader/actions/workflows/phpunit.yml/badge.svg?branch=master)](https://github.com/alexpts/php-symfony-di-loader/actions/workflows/phpunit.yml)
[![codecov](https://codecov.io/gh/alexpts/php-symfony-di-loader/branch/master/graph/badge.svg?token=14L6IJA5UE)](https://codecov.io/gh/alexpts/php-symfony-di-loader)

Simple loader for symfony container [DependencyInjection component]( https://symfony.com/doc/current/components/dependency_injection.html)

### Install
`composer require alexpts/symfony-di-loader`


### Demo
```php
<?php
use PTS\SymfonyDiLoader\LoaderContainer;

$configs = [
    __DIR__ . './container.yml',
    __DIR__ . './container.xml',
    __DIR__ . './container.php'
];
$fileCache = __DIR__ . '/var/di.php';
$isDebug = true;

$loader = new LoaderContainer;
$container = $loader->getContainer($configs, $fileCache, $isDebug);
```

Custom container extension:
```php
use PTS\SymfonyDiLoader\LoaderContainer;

$loader = new LoaderContainer;
$builder = $loader->getBuilder();
$builder->addExtension($someContainerExtension);
$container = $loader->getContainer($configs, $fileCache);
```
