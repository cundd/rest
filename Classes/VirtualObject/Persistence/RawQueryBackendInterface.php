<?php

namespace Cundd\Rest\VirtualObject\Persistence;

use Cundd\Rest\VirtualObject\Persistence\Exception\SqlErrorException;

/**
 * Interface for a Backend that supports raw database queries
 */
interface RawQueryBackendInterface
{
    /**
     * Perform a raw query on the database
     *
     * @param string $query
     * @return mixed
     * @throws SqlErrorException if the query failed
     */
    public function executeQuery($query);
}
