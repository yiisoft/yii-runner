<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Runner;

use ErrorException;
use Yiisoft\Config\Config;
use Yiisoft\Config\ConfigPaths;
use Yiisoft\Config\Modifier\RecursiveMerge;
use Yiisoft\Config\Modifier\ReverseMerge;

/**
 * Creates a Config instance for a given environment.
 */
final class ConfigFactory
{
    /**
     * @throws ErrorException If the environment does not exist.
     */
    public static function create(
        ConfigPaths $paths,
        ?string $environment,
        ?string $configGroupPostfix = null,
    ): Config {
        $paramsGroups = ['params'];
        if ($configGroupPostfix !== null) {
            $paramsGroups[] = 'params-' . $configGroupPostfix;
        }

        $eventGroups = ['events'];
        if ($configGroupPostfix !== null) {
            $eventGroups[] = 'events-' . $configGroupPostfix;
        }

        return new Config(
            $paths,
            $environment,
            [
                ReverseMerge::groups(...$eventGroups),
                RecursiveMerge::groups(...$paramsGroups, ...$eventGroups),
            ],
            'params',
        );
    }
}
