<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Runner;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Throwable;

/**
 * Re-throws throwable.
 */
final class ThrowableHandler implements RequestHandlerInterface
{
    private Throwable $throwable;

    public function __construct(Throwable $throwable)
    {
        $this->throwable = $throwable;
    }

    /**
     * @throws Throwable
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        throw $this->throwable;
    }
}
