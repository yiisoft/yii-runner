<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Runner\Tests;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use stdClass;
use Yiisoft\Config\Config;
use Yiisoft\Config\ConfigPaths;
use Yiisoft\Di\Container;
use Yiisoft\Di\ContainerConfig;
use Yiisoft\Yii\Event\InvalidListenerConfigurationException;
use Yiisoft\Yii\Runner\Tests\Support\ApplicationRunner\ApplicationRunner;
use Yiisoft\Yii\Runner\Tests\Support\ApplicationRunner\Support\Repository;

final class ApplicationRunnerTest extends TestCase
{
    public function testGetConfig(): void
    {
        $runner = new ApplicationRunner();
        $config = $runner->getRunnerConfig();

        $this->assertSame(['name' => 'John', 'age' => 42], $config->get('params'));
    }

    public function testGetConfigAndWithConfig(): void
    {
        $config = $this->createConfig();
        $runner = (new ApplicationRunner())->withConfig($config);

        $this->assertSame($config, $runner->getRunnerConfig());
    }

    public function testGetContainer(): void
    {
        $runner = new ApplicationRunner();
        $container = $runner->getRunnerContainer();
        $stdClass = $container->get(stdClass::class);

        $this->assertSame('John', $stdClass->name);
        $this->assertSame(42, $stdClass->age);
    }

    public function testGetContainerAndWithContainer(): void
    {
        $container = $this->createContainer();
        $runner = (new ApplicationRunner())->withContainer($container);

        $this->assertSame($container, $runner->getRunnerContainer());
    }

    public function testGetContainerAndWithNotYiiContainer(): void
    {
        $container = new class () implements ContainerInterface {
            public function get(string $id): mixed
            {
                return null;
            }

            public function has(string $id): bool
            {
                return false;
            }
        };

        $runner = (new ApplicationRunner())->withContainer($container);

        $this->assertSame($container, $runner->getRunnerContainer());
    }

    public function testTags(): void
    {
        $runner = new ApplicationRunner();
        $config = $runner->getRunnerConfig();

        $this->assertTrue($config->has('di-tags-web'));

        $container = $runner->getRunnerContainer();
        $repositories = $container->get('tag@repositories');

        $this->assertEquals([new Repository()], $repositories);
    }

    public function testRunBootstrap(): void
    {
        $runner = new ApplicationRunner();

        $this->expectOutputString('Bootstrapping');

        $runner->doRunBootstrap();
    }

    public function testCheckEvents(): void
    {
        $runner = new ApplicationRunner(eventsGroup: 'events-fail');

        $this->expectException(InvalidListenerConfigurationException::class);

        $runner->doCheckEvents();
    }

    public function testRun(): void
    {
        $this->expectOutputString('Bootstrapping');
        (new ApplicationRunner())->run();
    }

    public function testRunWithSetters(): void
    {
        $this->expectOutputString('Bootstrapping');

        (new ApplicationRunner())
            ->withContainer($this->createContainer())
            ->withConfig($this->createConfig())
            ->run();
    }

    public function testImmutability(): void
    {
        $runner = new ApplicationRunner();

        $this->assertNotSame($runner, $runner->withConfig($this->createConfig()));
        $this->assertNotSame($runner, $runner->withContainer($this->createContainer()));
    }

    private function createConfig(): Config
    {
        return new Config(new ConfigPaths(__DIR__ . '/Support/ApplicationRunner', 'config'));
    }

    private function createContainer(): Container
    {
        return new Container(ContainerConfig::create());
    }
}
