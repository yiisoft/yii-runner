<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Runner;

use Yiisoft\Config\Config;
use Yiisoft\Config\ConfigPaths;
use Yiisoft\Config\Modifier\RecursiveMerge;
use Yiisoft\Config\Modifier\ReverseMerge;

/**
 * Creates a Config instance for a given environment.
 */
final class ConfigFactory
{
    public static function create(string $rootPath, ?string $environment): Config
    {
        $eventGroups = [
            'events',
            'events-web',
            'events-console',
        ];

        return new Config(
            new ConfigPaths($rootPath, 'config'),
            $environment,
            [
                ReverseMerge::groups(...$eventGroups),
                RecursiveMerge::groups('params', ...$eventGroups),
            ],
        );
    }
}
