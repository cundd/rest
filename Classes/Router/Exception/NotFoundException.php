<?php

declare(strict_types=1);

namespace Cundd\Rest\Router\Exception;

use Cundd\Rest\Router\RouteInterface;
use Cundd\Rest\Utility\DebugUtility;
use RuntimeException;
use Throwable;

use function sprintf;

/**
 * An exception to signal that a Route was Not Found
 */
final class NotFoundException extends RuntimeException
{
    /**
     * @param RouteInterface[] $alternativeRoutes
     */
    public function __construct(
        string $message = '',
        int $code = 0,
        ?Throwable $previous = null,
        private readonly array $alternativeRoutes = [],
    ) {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Build a new NotFound Exception
     *
     * @param RouteInterface[] $alternativeRoutes
     *
     * @return static
     */
    public static function exceptionWithAlternatives(
        string $route,
        string $method,
        array $alternativeRoutes,
    ): self {
        $message = DebugUtility::allowDebugInformation()
            ? sprintf('Route "%s" not found for method "%s"', $route, $method)
            : '';

        return new static($message, 0, null, $alternativeRoutes);
    }

    /**
     * Return the suggestions for alternative routes
     *
     * @return RouteInterface[]
     */
    public function getAlternativeRoutes(): array
    {
        return $this->alternativeRoutes;
    }
}
