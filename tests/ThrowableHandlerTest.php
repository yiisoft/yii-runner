<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Runner\Tests;

use HttpSoft\Message\ServerRequest;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Yiisoft\Yii\Runner\ThrowableHandler;

use function get_class;

final class ThrowableHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        $exception = new RuntimeException('Some error.', 0);
        $handler = new ThrowableHandler($exception);

        $this->expectException(get_class($exception));
        $this->expectExceptionCode($exception->getCode());
        $this->expectExceptionMessage($exception->getMessage());

        $handler->handle(new ServerRequest());
    }
}
