<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Runner\Tests;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use stdClass;
use Yiisoft\Config\Config;
use Yiisoft\Config\ConfigInterface;
use Yiisoft\Config\ConfigPaths;
use Yiisoft\Di\Container;
use Yiisoft\Di\ContainerConfig;
use Yiisoft\Yii\Event\InvalidListenerConfigurationException;
use Yiisoft\Yii\Runner\ApplicationRunner;

final class ApplicationRunnerTest extends TestCase
{
    public function testCreateConfig(): void
    {
        $runner = $this->createApplicationRunner();
        $config = $runner->createConfig();

        $this->assertSame(['name' => 'John', 'age' => 42], $config->get('params'));
    }

    public function testCreateContainer(): void
    {
        $runner = $this->createApplicationRunner();
        $config = $runner->createConfig();
        $container = $runner->createContainer($config, 'web');
        $stdClass = $container->get(stdClass::class);

        $this->assertSame('John', $stdClass->name);
        $this->assertSame(42, $stdClass->age);
    }

    public function testRunBootstrap(): void
    {
        $runner = $this->createApplicationRunner()->withBootstrap('bootstrap-web');

        $this->expectOutputString('Bootstrapping');

        $runner->runBootstrap($this->createConfig(), $this->createContainer());
    }

    public function testCheckEvents(): void
    {
        $runner = $this->createApplicationRunner()->withCheckingEvents('events-fail');
        $config = $runner->createConfig();
        $container = $runner->createContainer($config, 'web');

        $this->expectException(InvalidListenerConfigurationException::class);

        $runner->checkEvents($config, $container);
    }

    public function testRun(): void
    {
        $this->expectOutputString('');
        $this->createApplicationRunner()->run();
    }

    public function testRunWithoutBootstrapAndCheckEvents(): void
    {
        $this->expectOutputString('');
        $this->createApplicationRunner()->withoutBootstrap()->withoutCheckingEvents()->run();
    }

    public function testRunWithSetters(): void
    {
        $this->expectOutputString('Bootstrapping');

        $this->createApplicationRunner()
            ->withCheckingEvents('events-web')
            ->withBootstrap('bootstrap-web')
            ->withContainer($this->createContainer())
            ->withConfig($this->createConfig())
            ->run()
        ;
    }

    public function testImmutability(): void
    {
        $runner = $this->createApplicationRunner();

        $this->assertNotSame($runner, $runner->withBootstrap('bootstrap-web'));
        $this->assertNotSame($runner, $runner->withoutBootstrap());
        $this->assertNotSame($runner, $runner->withCheckingEvents('events-web'));
        $this->assertNotSame($runner, $runner->withoutCheckingEvents());
        $this->assertNotSame($runner, $runner->withConfig($this->createConfig()));
        $this->assertNotSame($runner, $runner->withContainer($this->createContainer()));
    }

    private function createConfig(): Config
    {
        return new Config(new ConfigPaths(__DIR__ . '/Support/application-runner', 'config'));
    }

    private function createContainer(): ContainerInterface
    {
        return new Container(ContainerConfig::create());
    }

    private function createApplicationRunner(): ApplicationRunner
    {
        return new class () extends ApplicationRunner {
            public function __construct()
            {
                parent::__construct(__DIR__ . '/Support/application-runner', true, null);
            }

            public function run(): void
            {
                $config = $this->config ?? $this->createConfig();
                $container = $this->container ?? $this->createContainer($config, 'web');
                $this->runBootstrap($config, $container);
                $this->checkEvents($config, $container);
            }

            public function createConfig(): Config
            {
                return parent::createConfig();
            }

            public function createContainer(ConfigInterface $config, string $definitionEnvironment): Container
            {
                return parent::createContainer($config, $definitionEnvironment);
            }

            public function runBootstrap(ConfigInterface $config, ContainerInterface $container): void
            {
                parent::runBootstrap($config, $container);
            }

            public function checkEvents(ConfigInterface $config, ContainerInterface $container): void
            {
                parent::checkEvents($config, $container);
            }
        };
    }
}
