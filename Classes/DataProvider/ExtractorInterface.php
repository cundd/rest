<?php

declare(strict_types=1);

namespace Cundd\Rest\DataProvider;

use InvalidArgumentException;
use RuntimeException;

/**
 * Class to prepare/extract the data to be sent from objects
 */
interface ExtractorInterface
{
    /**
     * Returns the data from the given input
     *
     * @param mixed $input
     * @return mixed
     * @throws RuntimeException if the data nesting is too deep
     * @throws InvalidArgumentException if the input type is not supported
     */
    public function extract($input);
}
