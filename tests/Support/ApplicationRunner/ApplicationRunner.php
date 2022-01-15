<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Runner\Tests\Support\ApplicationRunner;

use Psr\Container\ContainerInterface;
use Yiisoft\Config\Config;
use Yiisoft\Config\ConfigInterface;
use Yiisoft\Di\Container;

final class ApplicationRunner extends \Yiisoft\Yii\Runner\ApplicationRunner
{
    public function __construct()
    {
        parent::__construct(__DIR__, true, null);
    }

    public function run(): void
    {
        $config = $this->getConfig();
        $container = $this->getContainer($config, 'web');
        $this->runBootstrap($config, $container);
        $this->checkEvents($config, $container);
    }

    public function runBootstrap(ConfigInterface $config, ContainerInterface $container): void
    {
        parent::runBootstrap($config, $container);
    }

    public function checkEvents(ConfigInterface $config, ContainerInterface $container): void
    {
        parent::checkEvents($config, $container);
    }

    public function getConfig(): ConfigInterface
    {
        return parent::getConfig();
    }

    public function getContainer(ConfigInterface $config, string $definitionEnvironment): ContainerInterface
    {
        return parent::getContainer($config, $definitionEnvironment);
    }

    public function createDefaultConfig(): Config
    {
        return parent::createDefaultConfig();
    }

    public function createDefaultContainer(ConfigInterface $config, string $definitionEnvironment): Container
    {
        return parent::createDefaultContainer($config, $definitionEnvironment);
    }
}
