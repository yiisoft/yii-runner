<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Runner\Tests;

use PHPUnit\Framework\TestCase;
use Yiisoft\Yii\Runner\ConfigFactory;

final class ConfigFactoryTest extends TestCase
{
    public function testCreate(): void
    {
        $config = ConfigFactory::create(__DIR__ . '/TestAsset', null);

        $this->assertSame([
            'e1' => [
                ['app1', 'handler1'],
            ],
            'e2' => [
                ['app2', 'handler2'],
            ],
        ], $config->get('events'));

        $this->assertSame([
            'e1' => [
                ['app3', 'handler3'],
                ['app1', 'handler1'],
            ],
            'e2' => [
                ['app2', 'handler2'],
            ],
        ], $config->get('events-console'));

        $this->assertSame([
            'e1' => [
                ['app4', 'handler4'],
                ['app1', 'handler1'],
            ],
            'e2' => [
                ['app2', 'handler2'],
            ],
        ], $config->get('events-web'));
    }
}
