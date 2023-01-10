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
        $config = $runner->getConfig();

        $this->assertSame(['name' => 'John', 'age' => 42], $config->get('params'));
    }

    public function testCreateDefaultConfig(): void
    {
        $runner = new ApplicationRunner();
        $config = $runner->createDefaultConfig();

        $this->assertSame(['name' => 'John', 'age' => 42], $config->get('params'));

        $config2 = $runner->createDefaultConfig();

        $this->assertNotSame($config, $config2);
        $this->assertSame(['name' => 'John', 'age' => 42], $config2->get('params'));
    }

    public function testGetConfigAndWithConfig(): void
    {
        $config = $this->createConfig();
        $runner = (new ApplicationRunner())->withConfig($config);

        $this->assertSame($config, $runner->getConfig());
    }

    public function testGetContainer(): void
    {
        $runner = new ApplicationRunner();
        $config = $runner->getConfig();
        $container = $runner->getContainer();
        $stdClass = $container->get(stdClass::class);

        $this->assertSame('John', $stdClass->name);
        $this->assertSame(42, $stdClass->age);
    }

    public function testCreateDefaultContainer(): void
    {
        $runner = new ApplicationRunner();
        $config = $runner->getConfig();

        $container = $runner->createDefaultContainer($config, 'web');
        $stdClass = $container->get(stdClass::class);

        $this->assertSame('John', $stdClass->name);
        $this->assertSame(42, $stdClass->age);

        $container2 = $runner->createDefaultContainer($config, 'web');
        $stdClass2 = $container2->get(stdClass::class);

        $this->assertNotSame($container, $container2);
        $this->assertNotSame($stdClass, $stdClass2);

        $this->assertSame('John', $stdClass2->name);
        $this->assertSame(42, $stdClass2->age);
    }

    public function testGetContainerAndWithContainer(): void
    {
        $container = $this->createContainer();
        $runner = (new ApplicationRunner())->withContainer($container);

        $this->assertSame($container, $runner->getContainer());
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

        $this->assertSame($container, $runner->getContainer());
    }

    public function testTags(): void
    {
        $runner = new ApplicationRunner();
        $config = $runner->getConfig();

        $this->assertTrue($config->has('tags-web'));

        $container = $runner->createDefaultContainer($config, 'web');
        $repositories = $container->get('tag@repositories');

        $this->assertEquals([new Repository()], $repositories);
    }

    public function testRunBootstrap(): void
    {
        $runner = (new ApplicationRunner())->withBootstrap('bootstrap-web');

        $this->expectOutputString('Bootstrapping');

        $runner->runBootstrap($this->createConfig(), $this->createContainer());
    }

    public function testCheckEvents(): void
    {
        $runner = (new ApplicationRunner())->withCheckingEvents('events-fail');
        $config = $runner->getConfig();
        $container = $runner->getContainer();

        $this->expectException(InvalidListenerConfigurationException::class);

        $runner->checkEvents($config, $container);
    }

    public function testRun(): void
    {
        $this->expectOutputString('');
        (new ApplicationRunner())->run();
    }

    public function testRunWithoutBootstrapAndCheckEvents(): void
    {
        $this->expectOutputString('');
        (new ApplicationRunner())
            ->withoutBootstrap()
            ->withoutCheckingEvents()
            ->run();
    }

    public function testRunWithSetters(): void
    {
        $this->expectOutputString('Bootstrapping');

        (new ApplicationRunner())
            ->withCheckingEvents('events-web')
            ->withBootstrap('bootstrap-web')
            ->withContainer($this->createContainer())
            ->withConfig($this->createConfig())
            ->run()
        ;
    }

    public function testImmutability(): void
    {
        $runner = new ApplicationRunner();

        $this->assertNotSame($runner, $runner->withBootstrap('bootstrap-web'));
        $this->assertNotSame($runner, $runner->withoutBootstrap());
        $this->assertNotSame($runner, $runner->withCheckingEvents('events-web'));
        $this->assertNotSame($runner, $runner->withoutCheckingEvents());
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
