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
use Yiisoft\Config\Modifier\RecursiveMerge;
use Yiisoft\Config\Modifier\ReverseMerge;
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
     * @param string|null $environment The environment name.
     *
     * @psalm-param list<string> $nestedParamsGroups
     * @psalm-param list<string> $nestedEventsGroups
     */
    public function __construct(
        protected string $rootPath,
        protected bool $debug,
        protected bool $checkEvents,
        protected ?string $environment,
        protected string $bootstrapGroup,
        protected string $eventsGroup,
        protected string $diGroup,
        protected string $diProvidersGroup,
        protected string $diDelegatesGroup,
        protected string $diTagsGroup,
        protected string $paramsGroup,
        protected array $nestedParamsGroups,
        protected array $nestedEventsGroups,
    ) {
    }

    abstract public function run(): void;

    /**
     * Returns a new instance with the specified config instance {@see ConfigInterface}.
     *
     * @param ConfigInterface $config The config instance.
     */
    final public function withConfig(ConfigInterface $config): static
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
    final public function withContainer(ContainerInterface $container): static
    {
        $new = clone $this;
        $new->container = $container;
        return $new;
    }

    /**
     * @throws ErrorException|RuntimeException
     */
    final protected function runBootstrap(): void
    {
        $bootstrapList = $this->getConfiguration($this->bootstrapGroup);
        if ($bootstrapList === null) {
            return;
        }

        (new BootstrapRunner($this->getContainer(), $bootstrapList))->run();
    }

    /**
     * @throws ContainerExceptionInterface|ErrorException|NotFoundExceptionInterface
     */
    final protected function checkEvents(): void
    {
        if ($this->debug && $this->checkEvents) {
            $configuration = $this->getConfiguration($this->eventsGroup);
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
    final protected function getConfig(): ConfigInterface
    {
        return $this->config ??= $this->createDefaultConfig();
    }

    /**
     * @throws ErrorException|InvalidConfigException
     */
    final protected function getContainer(): ContainerInterface
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
    private function createDefaultConfig(): Config
    {
        $paramsGroups = [$this->paramsGroup, ...$this->nestedParamsGroups];
        $eventsGroups = [$this->eventsGroup, ...$this->nestedEventsGroups];

        return new Config(
            new ConfigPaths($this->rootPath, 'config'),
            $this->environment,
            [
                ReverseMerge::groups(...$eventsGroups),
                RecursiveMerge::groups(...$paramsGroups, ...$eventsGroups),
            ],
            $this->paramsGroup,
        );
    }

    /**
     * @throws ErrorException|InvalidConfigException
     */
    private function createDefaultContainer(): Container
    {
        $containerConfig = ContainerConfig::create()->withValidate($this->debug);

        $config = $this->getConfig();

        if (null !== $definitions = $this->getConfiguration($this->diGroup)) {
            $containerConfig = $containerConfig->withDefinitions($definitions);
        }

        if (null !== $providers = $this->getConfiguration($this->diProvidersGroup)) {
            $containerConfig = $containerConfig->withProviders($providers);
        }

        if (null !== $delegates = $this->getConfiguration($this->diDelegatesGroup)) {
            $containerConfig = $containerConfig->withDelegates($delegates);
        }

        if (null !== $tags = $this->getConfiguration($this->diTagsGroup)) {
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
        return $config->has($name) ? $config->get($name) : null;
    }
}
