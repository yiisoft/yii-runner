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
        bool $checkEvents = true,
        string $eventsGroup = 'events-web',
    ) {
        parent::__construct(
            rootPath: __DIR__,
            debug: true,
            checkEvents: $checkEvents,
            environment: null,
            bootstrapGroup: 'bootstrap-web',
            eventsGroup: $eventsGroup,
            diGroup: 'di-web',
            diProvidersGroup: 'di-providers-web',
            diDelegatesGroup: 'di-delegates-web',
            diTagsGroup: 'di-tags-web',
            paramsGroup: 'params',
            nestedParamsGroups: [],
            nestedEventsGroups: ['events'],
        );
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
