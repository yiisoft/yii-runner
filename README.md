<p align="center">
    <a href="https://github.com/yiisoft" target="_blank">
        <img src="https://yiisoft.github.io/docs/images/yii_logo.svg" height="100px">
    </a>
    <h1 align="center">Yii application runner</h1>
    <br>
</p>

[![Latest Stable Version](https://poser.pugx.org/yiisoft/yii-runner/v/stable.png)](https://packagist.org/packages/yiisoft/yii-runner)
[![Total Downloads](https://poser.pugx.org/yiisoft/yii-runner/downloads.png)](https://packagist.org/packages/yiisoft/yii-runner)
[![Build status](https://github.com/yiisoft/yii-runner/workflows/build/badge.svg)](https://github.com/yiisoft/yii-runner/actions?query=workflow%3Abuild)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/yiisoft/yii-runner/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/yiisoft/yii-runner/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/yiisoft/yii-runner/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/yiisoft/yii-runner/?branch=master)
[![Mutation testing badge](https://img.shields.io/endpoint?style=flat&url=https%3A%2F%2Fbadge-api.stryker-mutator.io%2Fgithub.com%2Fyiisoft%2Fyii-runner%2Fmaster)](https://dashboard.stryker-mutator.io/reports/github.com/yiisoft/yii-runner/master)
[![static analysis](https://github.com/yiisoft/yii-runner/workflows/static%20analysis/badge.svg)](https://github.com/yiisoft/yii-runner/actions?query=workflow%3A%22static+analysis%22)
[![type-coverage](https://shepherd.dev/github/yiisoft/yii-runner/coverage.svg)](https://shepherd.dev/github/yiisoft/yii-runner)

The package defines Yii application runner. A runner hides application initialization details making configuration
process easier.

## Requirements

- PHP 8.0 or higher.

## Installation

The package could be installed with composer:

```shell
composer require yiisoft/yii-runner --prefer-dist
```

## General usage

Install one of the adapters:

- [Console](https://github.com/yiisoft/yii-runner-console)
- [HTTP](https://github.com/yiisoft/yii-runner-http)
- [RoadRunner](https://github.com/yiisoft/yii-runner-roadrunner)

Instantiate and run it in an entry script:

```php
<?php

declare(strict_types=1);

use Yiisoft\Yii\Runner\Http\HttpApplicationRunner;

require_once __DIR__ . '/autoload.php';

(new HttpApplicationRunner(__DIR__, $_ENV['YII_DEBUG'], $_ENV['YII_ENV']))->run();
```

## ApplicationRunner creates config and default dependency injection container

### Create configs

In the ```parent abstract class ApplicationRunner``` of the adapters creates environment configs
for one of the ```development```, ```production``` or ```other``` environments you create


```php 
<?php

declare(strict_types=1);

abstract class ApplicationRunner {
    protected function createDefaultConfig(): Config
    {
        return ConfigFactory::create(new ConfigPaths($this->rootPath, 'config'), $this->environment);
    }
}

```

Builds configs [package configs](https://github.com/yiisoft/config), the package supports the creation of
```environments``` and ```groups``` of configs


#### Config groups

You can also set up config groups for the ```web``` or ```console```

```json
"extra": {
    "config-plugin": {
        "params": [
            "params.php",
            "?params-local.php"
        ],
        "common": "common.php",
        "web": [
            "$common",
            "web.php",
            "../src/Modules/*/config/web.php"
        ],
        "console": [
            "$common",
            "console.php",
        ]
    }
}
```

#### Config environment

The config package supports setting up environments such as ```development``` or ```production```

```json
"extra": {
    "config-plugin-options": {
        "source-directory": "config"
    },
    "config-plugin": {
        "params": "params.php",
        "web": "web.php"
    },
    "config-plugin-environments": {
        "dev": {
            "params": "dev/params.php",
            "app": [
                "$web",
                "dev/app.php"
            ]
        },
        "prod": {
            "app": "prod/app.php"
        }
    }
}
```

Learn more about setting up and building configs in [Config package](https://github.com/yiisoft/config)

## DI container

After creating the configuration, ApplicationRunner creates a default dependency injection container,
a ```group``` of configs from the configured ```environment``` is added to the container.

```php
protected function createDefaultContainer(ConfigInterface $config, string $definitionEnvironment): Container
{
    $containerConfig = ContainerConfig::create()->withValidate($this->debug);

    if ($config->has($definitionEnvironment)) {
        $containerConfig = $containerConfig->withDefinitions($config->get($definitionEnvironment));
    }

    if ($config->has("providers-$definitionEnvironment")) {
        $containerConfig = $containerConfig->withProviders($config->get("providers-$definitionEnvironment"));
    }

    if ($config->has("delegates-$definitionEnvironment")) {
        $containerConfig = $containerConfig->withDelegates($config->get("delegates-$definitionEnvironment"));
    }

    if ($config->has("tags-$definitionEnvironment")) {
        $containerConfig = $containerConfig->withTags($config->get("tags-$definitionEnvironment"));
    }

    $containerConfig = $containerConfig->withDefinitions(
        array_merge($containerConfig->getDefinitions(), [ConfigInterface::class => $config])
    );

    return new Container($containerConfig);
}
```
At the beginning, an array of definitions for the container is initialized

Example definitions:
```php
return [
    EngineInterface::class => EngineMarkOne::class,
    'full_definition' => [
        'class' => EngineMarkOne::class,
        '__construct()' => [42],
        '$propertyName' => 'value',
        'setX()' => [42],
    ],
    'closure' => fn (SomeFactory $factory) => $factory->create('args'),
    'static_call_preferred' => fn () => MyFactory::create('args'),
    'static_call_supported' => [MyFactory::class, 'create'],
    'object' => new MyClass(),
];
```

Next, the array of providers is initialized

```php
return [
    CarFactoryProvider::class,
    GarageFactoryProvider::class,
]
```
Example provider: 
```php
use Yiisoft\Di\Container;
use Yiisoft\Di\ServiceProviderInterface;

class CarFactoryProvider extends ServiceProviderInterface
{
    public function getDefinitions(): array
    {
        return [
            CarFactory::class => [
                'class' => CarFactory::class,
                '$color' => 'red',
            ], 
            EngineInterface::class => SolarEngine::class,
            WheelInterface::class => [
                'class' => Wheel::class,
                '$color' => 'black',
            ],
            CarInterface::class => [
                'class' => BMW::class,
                '$model' => 'X5',
            ],
        ];    
    }
     
    public function getExtensions(): array
    {
        return [
            // Note that Garage should already be defined in container 
            Garage::class => function(ContainerInterface $container, Garage $garage) {
                $car = $container
                    ->get(CarFactory::class)
                    ->create();
                $garage->setCar($car);
                
                return $garage;
            }
        ];
    } 
}
```
Then, an array of delegate containers is initialized, in which it is possible to get definitions
if definitions were not found in the main container.

To configure delegates, use an additional config:

```php
use Yiisoft\Di\Container;
use Yiisoft\Di\ContainerConfig;

$config = ContainerConfig::create()
    ->withDelegates([
        function (ContainerInterface $container): ContainerInterface {
            // ...
        }
    ]);


$container = new Container($config);
```

Finally, an array of definition tags is initialized:

```php
return [
    'command-services' => [ CreateUser::class, DeleteUser::class ]
    'query-services' => [ GetAllUsers::class, GetUser::class ]
]
```

For more information on creating container definitions, see [DI container](https://github.com/yiisoft/di)

## Testing

### Unit testing

The package is tested with [PHPUnit](https://phpunit.de/). To run tests:

```shell
./vendor/bin/phpunit
```

### Mutation testing

The package tests are checked with [Infection](https://infection.github.io/) mutation framework with
[Infection Static Analysis Plugin](https://github.com/Roave/infection-static-analysis-plugin). To run it:

```shell
./vendor/bin/roave-infection-static-analysis-plugin
```

### Static analysis

The code is statically analyzed with [Psalm](https://psalm.dev/). To run static analysis:

```shell
./vendor/bin/psalm
```

## License

The Yii Runner is free software. It is released under the terms of the BSD License.
Please see [`LICENSE`](./LICENSE.md) for more information.

Maintained by [Yii Software](https://www.yiiframework.com/).

## Support the project

[![Open Collective](https://img.shields.io/badge/Open%20Collective-sponsor-7eadf1?logo=open%20collective&logoColor=7eadf1&labelColor=555555)](https://opencollective.com/yiisoft)

## Follow updates

[![Official website](https://img.shields.io/badge/Powered_by-Yii_Framework-green.svg?style=flat)](https://www.yiiframework.com/)
[![Twitter](https://img.shields.io/badge/twitter-follow-1DA1F2?logo=twitter&logoColor=1DA1F2&labelColor=555555?style=flat)](https://twitter.com/yiiframework)
[![Telegram](https://img.shields.io/badge/telegram-join-1DA1F2?style=flat&logo=telegram)](https://t.me/yii3en)
[![Facebook](https://img.shields.io/badge/facebook-join-1DA1F2?style=flat&logo=facebook&logoColor=ffffff)](https://www.facebook.com/groups/yiitalk)
[![Slack](https://img.shields.io/badge/slack-join-1DA1F2?style=flat&logo=slack)](https://yiiframework.com/go/slack)
