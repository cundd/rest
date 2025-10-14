<?php

declare(strict_types=1);

namespace Cundd\Rest\DataProvider;

use InvalidArgumentException;
use RuntimeException;

/**
 * Class to prepare/extract the data to be sent from objects
 *
 * @phpstan-type Data string|int|bool|float|array<mixed,mixed>|null
 */
interface ExtractorInterface
{
    /**
     * Extract the data from the given input
     *
     * @return Data
     *
     * @throws RuntimeException         if the data nesting is too deep
     * @throws InvalidArgumentException if the input type is not supported
     */
    public function extract(mixed $input): string|int|bool|float|array|null;
}
