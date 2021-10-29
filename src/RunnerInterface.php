<?php

namespace Yiisoft\Yii\Runner;

/**
 * Runs an application hiding initialization details.
 */
interface RunnerInterface
{
    /**
     * Runs an application.
     */
    public function run(): void;
}
