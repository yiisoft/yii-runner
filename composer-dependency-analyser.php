<?php

declare(strict_types=1);

use ShipMonk\ComposerDependencyAnalyser\Config\Configuration;
use Yiisoft\Definitions\Exception\InvalidConfigException;

return (new Configuration())
    ->disableComposerAutoloadPathScan()
    ->setFileExtensions(['php'])
    ->addPathToScan(__DIR__ . '/src', isDev: false)
    ->addPathToScan(__DIR__ . '/tests', isDev: true)
    // Only referenced via `@throws` PHPDoc tags in src/ApplicationRunner.php, not detected by static scanning.
    ->addForceUsedSymbol(InvalidConfigException::class);
