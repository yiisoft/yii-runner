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
     * @return self
     */
    public function withBootstrap(string $bootstrapGroup): self
    {
        $new = clone $this;
        $new->bootstrapGroup = $bootstrapGroup;
        return $new;
    }

    /**
     * Returns a new instance and disables the use of bootstrap configuration group.
     *
     * @return self
     */
    public function withoutBootstrap(): self
    {
        $new = clone $this;
        $new->bootstrapGroup = null;
        return $new;
    }

    /**
     * Returns a new instance with the specified configuration group of events name for check.
     *
     * Note: The configuration of events is checked only in debug mode.
     *
     * @param string $eventsGroup The configuration group name of events for check.
     *
     * @return self
     */
    public function withCheckingEvents(string $eventsGroup): self
    {
        $new = clone $this;
        $new->eventsGroup = $eventsGroup;
        return $new;
    }

    /**
     * Returns a new instance and disables checking of the event configuration group.
     *
     * Note: The configuration of events is checked only in debug mode.
     *
     * @return self
     */
    public function withoutCheckingEvents(): self
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
     * @return self
     */
    public function withConfig(ConfigInterface $config): self
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
     * @return self
     */
    public function withContainer(ContainerInterface $container): self
    {
        $new = clone $this;
        $new->container = $container;
        return $new;
    }

    /**
     * @throws ErrorException
     */
    protected function createConfig(): Config
    {
        return ConfigFactory::create(new ConfigPaths($this->rootPath, 'config'), $this->environment);
    }

    /**
     * @throws ErrorException|InvalidConfigException
     */
    protected function createContainer(ConfigInterface $config, string $definitionEnvironment): Container
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
     * @throws ErrorException|ContainerExceptionInterface|NotFoundExceptionInterface
     */
    protected function checkEvents(ConfigInterface $config, ContainerInterface $container): void
    {
        if ($this->debug && $this->eventsGroup !== null) {
            /** @psalm-suppress MixedMethodCall */
            $container->get(ListenerConfigurationChecker::class)->check($config->get($this->eventsGroup));
        }
    }
}
