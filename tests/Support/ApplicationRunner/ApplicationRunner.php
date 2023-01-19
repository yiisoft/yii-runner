<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Runner\Tests\Support\ApplicationRunner;

use Psr\Container\ContainerInterface;
use Yiisoft\Config\Config;
use Yiisoft\Config\ConfigInterface;
use Yiisoft\Di\Container;

final class ApplicationRunner extends \Yiisoft\Yii\Runner\ApplicationRunner
{
    public function __construct(
        ?string $configGroupNamePostfix = 'web',
        bool $useBootstrap = true,
        bool $checkEvents = true,
    )
    {
        parent::__construct(__DIR__, true, $useBootstrap, $checkEvents, $configGroupNamePostfix, null);
    }

    public function run(): void
    {
        $this->runBootstrap();
        $this->checkEvents();
    }

    public function runBootstrap(): void
    {
        parent::runBootstrap();
    }

    public function checkEvents(): void
    {
        parent::checkEvents();
    }

    public function getConfig(): ConfigInterface
    {
        return parent::getConfig();
    }

    public function getContainer(): ContainerInterface
    {
        return parent::getContainer();
    }

    public function createDefaultConfig(): Config
    {
        return parent::createDefaultConfig();
    }

    public function createDefaultContainer(): Container
    {
        return parent::createDefaultContainer();
    }
}
