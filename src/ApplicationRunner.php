<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Runner;

use ErrorException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use RuntimeException;
use Yiisoft\Config\Config;
use Yiisoft\Config\ConfigInterface;
use Yiisoft\Config\ConfigPaths;
use Yiisoft\Definitions\Exception\InvalidConfigException;
use Yiisoft\Di\Container;
use Yiisoft\Di\ContainerConfig;
use Yiisoft\Yii\Event\ListenerConfigurationChecker;

/**
 * Provides basic functionality for creating adapters.
 */
abstract class ApplicationRunner implements RunnerInterface
{
    protected bool $debug;
    protected string $rootPath;
    protected ?string $environment;
    protected ?ConfigInterface $config = null;
    protected ?ContainerInterface $container = null;
    protected ?string $bootstrapGroup = null;
    protected ?string $eventsGroup = null;

    /**
     * @param string $rootPath The absolute path to the project root.
     * @param bool $debug Whether the debug mode is enabled.
     * @param string|null $environment The environment name.
     */
    public function __construct(string $rootPath, bool $debug, ?string $environment)
    {
        $this->rootPath = $rootPath;
        $this->debug = $debug;
        $this->environment = $environment;
    }

    abstract public function run(): void;

    /**
     * Returns a new instance with the specified bootstrap configuration group name.
     *
     * @param string $bootstrapGroup The bootstrap configuration group name.
     *
     * @return static
     */
    public function withBootstrap(string $bootstrapGroup): static
    {
        $new = clone $this;
        $new->bootstrapGroup = $bootstrapGroup;
        return $new;
    }

    /**
     * Returns a new instance with bootstrapping disabled.
     *
     * @return static
     */
    public function withoutBootstrap(): static
    {
        $new = clone $this;
        $new->bootstrapGroup = null;
        return $new;
    }

    /**
     * Returns a new instance with the specified name of event configuration group to check.
     *
     * Note: The configuration of events is checked in debug mode only.
     *
     * @param string $eventsGroup Name of event configuration group to check.
     *
     * @return static
     */
    public function withCheckingEvents(string $eventsGroup): static
    {
        $new = clone $this;
        $new->eventsGroup = $eventsGroup;
        return $new;
    }

    /**
     * Returns a new instance with disabled event configuration check.
     *
     * @return static
     */
    public function withoutCheckingEvents(): static
    {
        $new = clone $this;
        $new->eventsGroup = null;
        return $new;
    }

    /**
     * Returns a new instance with the specified config instance {@see ConfigInterface}.
     *
     * @param ConfigInterface $config The config instance.
     *
     * @return static
     */
    public function withConfig(ConfigInterface $config): static
    {
        $new = clone $this;
        $new->config = $config;
        return $new;
    }

    /**
     * Returns a new instance with the specified container instance {@see ContainerInterface}.
     *
     * @param ContainerInterface $container The container instance.
     *
     * @return static
     */
    public function withContainer(ContainerInterface $container): static
    {
        $new = clone $this;
        $new->container = $container;
        return $new;
    }

    /**
     * @throws ErrorException|RuntimeException
     */
    protected function runBootstrap(ConfigInterface $config, ContainerInterface $container): void
    {
        if ($this->bootstrapGroup !== null) {
            (new BootstrapRunner($container, $config->get($this->bootstrapGroup)))->run();
        }
    }

    /**
     * @throws ContainerExceptionInterface|ErrorException|NotFoundExceptionInterface
     */
    protected function checkEvents(ConfigInterface $config, ContainerInterface $container): void
    {
        if ($this->debug && $this->eventsGroup !== null) {
            /** @psalm-suppress MixedMethodCall */
            $container->get(ListenerConfigurationChecker::class)->check($config->get($this->eventsGroup));
        }
    }

    /**
     * @throws ErrorException
     */
    protected function getConfig(): ConfigInterface
    {
        return $this->config ??= $this->createDefaultConfig();
    }

    /**
     * @throws ErrorException|InvalidConfigException
     */
    protected function getContainer(ConfigInterface $config, string $definitionEnvironment): ContainerInterface
    {
        $this->container ??= $this->createDefaultContainer($config, $definitionEnvironment);

        if ($this->container instanceof Container) {
            return $this->container->get(ContainerInterface::class);
        }

        return $this->container;
    }

    /**
     * @throws ErrorException
     */
    protected function createDefaultConfig(): Config
    {
        return ConfigFactory::create(new ConfigPaths($this->rootPath, 'config'), $this->environment);
    }

    /**
     * @throws ErrorException|InvalidConfigException
     */
    protected function createDefaultContainer(ConfigInterface $config, string $definitionEnvironment): Container
    {
        $containerConfig = ContainerConfig::create()->withValidate($this->debug);

        if ($config->has($definitionEnvironment)) {
            $containerConfig = $containerConfig->withDefinitions($config->get($definitionEnvironment));
        }

        if ($config->has("providers-$definitionEnvironment")) {
            $containerConfig = $containerConfig->withProviders($config->get("providers-$definitionEnvironment"));
        }

        if ($config->has("delegates-$definitionEnvironment")) {
            $containerConfig = $containerConfig->withDelegates($config->get("delegates-$definitionEnvironment"));
        }

        return new Container($containerConfig);
    }
}
