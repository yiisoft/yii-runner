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
    private ?ConfigInterface $config = null;
    private ?ContainerInterface $container = null;

    /**
     * @param string $rootPath The absolute path to the project root.
     * @param bool $debug Whether the debug mode is enabled.
     * @param string|null $configGroupPostfix A configuration groups postfix.
     * @param string|null $environment The environment name.
     */
    public function __construct(
        protected string $rootPath,
        protected bool $debug,
        protected bool $checkEvents,
        protected ?string $configGroupPostfix,
        protected ?string $environment,
    ) {
    }

    abstract public function run(): void;

    /**
     * Returns a new instance with the specified config instance {@see ConfigInterface}.
     *
     * @param ConfigInterface $config The config instance.
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
    protected function runBootstrap(): void
    {
        $bootstrapList = $this->getConfiguration('bootstrap');
        if ($bootstrapList === null) {
            return;
        }

        (new BootstrapRunner($this->getContainer(), $bootstrapList))->run();
    }

    /**
     * @throws ContainerExceptionInterface|ErrorException|NotFoundExceptionInterface
     */
    protected function checkEvents(): void
    {
        if ($this->debug && $this->checkEvents) {
            $configuration = $this->getConfiguration('events');
            if ($configuration !== null) {
                /** @psalm-suppress MixedMethodCall */
                $this->getContainer()
                    ->get(ListenerConfigurationChecker::class)
                    ->check($configuration);
            }
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
    protected function getContainer(): ContainerInterface
    {
        $this->container ??= $this->createDefaultContainer();

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
        return ConfigFactory::create(
            new ConfigPaths($this->rootPath, 'config'),
            $this->environment,
            $this->configGroupPostfix,
        );
    }

    /**
     * @throws ErrorException|InvalidConfigException
     */
    protected function createDefaultContainer(): Container
    {
        $containerConfig = ContainerConfig::create()->withValidate($this->debug);

        $config = $this->getConfig();

        if (null !== $definitions = $this->getConfiguration('di')) {
            $containerConfig = $containerConfig->withDefinitions($definitions);
        }

        if (null !== $providers = $this->getConfiguration('di-providers')) {
            $containerConfig = $containerConfig->withProviders($providers);
        }

        if (null !== $delegates = $this->getConfiguration('di-delegates')) {
            $containerConfig = $containerConfig->withDelegates($delegates);
        }

        if (null !== $tags = $this->getConfiguration('di-tags')) {
            $containerConfig = $containerConfig->withTags($tags);
        }

        $containerConfig = $containerConfig->withDefinitions(
            array_merge($containerConfig->getDefinitions(), [ConfigInterface::class => $config])
        );

        return new Container($containerConfig);
    }

    final protected function getConfiguration(string $name): ?array
    {
        $config = $this->getConfig();

        if ($this->configGroupPostfix !== null) {
            $fullName = $name . '-' . $this->configGroupPostfix;
            if ($config->has($fullName)) {
                return $config->get($fullName);
            }
        }

        if ($config->has($name)) {
            return $config->get($name);
        }

        return null;
    }
}
