<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Runner\Tests;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use RuntimeException;
use stdClass;
use Yiisoft\Test\Support\Container\SimpleContainer;
use Yiisoft\Yii\Runner\BootstrapRunner;

final class BootstrapRunnerTest extends TestCase
{
    public function testRun(): void
    {
        $stdClass = new stdClass();
        $stdClass->name = null;
        $stdClass->age = null;

        $runner = new BootstrapRunner(new SimpleContainer([stdClass::class => $stdClass]), [
            static function (ContainerInterface $container) {
                $stdClass = $container->get(stdClass::class);
                $stdClass->name = 'John';
                $stdClass->age = 42;
            },
        ]);

        $this->assertNull($stdClass->name);
        $this->assertNull($stdClass->age);

        $runner->run();

        $this->assertSame('John', $stdClass->name);
        $this->assertSame(42, $stdClass->age);
    }

    public function testRunFailure(): void
    {
        $runner = new BootstrapRunner(new SimpleContainer(), ['not-callable']);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Bootstrap callback must be callable, "string" given.');

        $runner->run();
    }
}
