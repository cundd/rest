<?php

declare(strict_types=1);

namespace Cundd\Rest\Request;

use Psr\Http\Message\RequestInterface;

enum RequestType
{
    case Read;
    case Write;
    case Preflight;

    public static function fromMethod(string $method): self
    {
        $methodUppercase = strtoupper($method);
        if ('OPTIONS' === $methodUppercase) {
            return self::Preflight;
        }

        if (in_array($methodUppercase, ['GET', 'HEAD'])) {
            return self::Read;
        }

        return self::Write;
    }

    public static function fromRequest(RequestInterface $request): self
    {
        return static::fromMethod($request->getMethod());
    }

    public function isPreflight(): bool
    {
        return RequestType::Preflight === $this;
    }

    public function isWrite(): bool
    {
        return RequestType::Write === $this;
    }

    public function isRead(): bool
    {
        return RequestType::Read === $this;
    }
}
