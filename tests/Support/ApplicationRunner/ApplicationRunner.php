<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Runner\Tests\Support\ApplicationRunner;

use Psr\Container\ContainerInterface;
use Yiisoft\Config\Config;

final class ApplicationRunner extends \Yiisoft\Yii\Runner\ApplicationRunner
{
    public function __construct(
        bool $checkEvents = true,
        string $eventsGroup = 'events-web',
        ?string $bootstrapGroup = 'bootstrap-web',
        array $configModifiers = [],
    ) {
        parent::__construct(
            rootPath: __DIR__,
            debug: true,
            checkEvents: $checkEvents,
            environment: null,
            bootstrapGroup: $bootstrapGroup,
            eventsGroup: $eventsGroup,
            diGroup: 'di-web',
            diProvidersGroup: 'di-providers-web',
            diDelegatesGroup: 'di-delegates-web',
            diTagsGroup: 'di-tags-web',
            paramsGroup: 'params',
            nestedParamsGroups: [],
            nestedEventsGroups: ['events', 'events-more'],
            configModifiers: $configModifiers,
        );
    }

    public function run(): void
    {
        $this->runBootstrap();
        $this->checkEvents();
    }

    public function doCheckEvents(): void
    {
        $this->checkEvents();
    }

    public function doRunBootstrap(): void
    {
        $this->runBootstrap();
    }

    public function getRunnerConfig(): Config
    {
        return $this->getConfig();
    }

    public function getRunnerContainer(): ContainerInterface
    {
        return $this->getContainer();
    }
}
