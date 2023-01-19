<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Runner\Tests;

use PHPUnit\Framework\TestCase;
use Yiisoft\Config\ConfigPaths;
use Yiisoft\Yii\Runner\ConfigFactory;

final class ConfigFactoryTest extends TestCase
{
    public function dataCreate(): array
    {
        return [
            [
                [
                    'e1' => [
                        ['app1', 'handler1'],
                        ['before-app', 'before-handler'],
                    ],
                    'e2' => [
                        ['app2', 'handler2'],
                        ['before-app', 'before-handler'],
                    ],
                ],
                null,
                'events',
            ],
            [
                [
                    'e1' => [
                        ['app3', 'handler3'],
                        ['app1', 'handler1'],
                        ['before-app', 'before-handler'],
                    ],
                    'e2' => [
                        ['app2', 'handler2'],
                        ['before-app', 'before-handler'],
                    ],
                ],
                'console',
                'events-console',
            ],
            [
                [
                    'e1' => [
                        ['app4', 'handler4'],
                        ['app1', 'handler1'],
                        ['before-app', 'before-handler'],
                    ],
                    'e2' => [
                        ['app2', 'handler2'],
                        ['before-app', 'before-handler'],
                    ],
                ],
                'web',
                'events-web',
            ],
        ];
    }

    /**
     * @dataProvider dataCreate
     */
    public function testCreate(array $expectedConfiguration, ?string $postfix, string $name): void
    {
        $config = ConfigFactory::create(new ConfigPaths(__DIR__ . '/Support/ConfigFactory', 'config'), null, $postfix);

        $this->assertSame($expectedConfiguration, $config->get($name));
    }
}
