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
class NotFoundException extends RuntimeException
{
    /**
     * @var RouteInterface[]
     */
    private $alternativeRoutes;

    /**
     * NotFoundException constructor
     *
     * @param string           $message
     * @param int              $code
     * @param Throwable|null   $previous
     * @param RouteInterface[] $alternativeRoutes
     */
    public function __construct($message = "", $code = 0, Throwable $previous = null, array $alternativeRoutes = [])
    {
        parent::__construct($message, $code, $previous);
        $this->alternativeRoutes = $alternativeRoutes;
    }

    /**
     * Build a new NotFound Exception
     *
     * @param string $route
     * @param string $method
     * @param array  $alternativeRoutes
     * @return static
     */
    public static function exceptionWithAlternatives(string $route, string $method, array $alternativeRoutes): self
    {
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
