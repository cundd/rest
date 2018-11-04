<?php

namespace Cundd\Rest\VirtualObject\Persistence\Backend;

use Cundd\Rest\VirtualObject\Persistence\Exception\SqlErrorException;
use Cundd\Rest\VirtualObject\Persistence\QueryInterface;
use Doctrine\DBAL\DBALException;
use TYPO3\CMS\Core\Database\ConnectionPool;

class DoctrineBackend extends AbstractBackend
{
    /**
     * @var ConnectionPool
     */
    private $connectionPool;

    /**
     * @var WhereClauseBuilder
     */
    private $whereClauseBuilder;

    /**
     * DoctrineConnection constructor
     *
     * @param ConnectionPool     $connectionPool
     * @param WhereClauseBuilder $whereClauseBuilder
     */
    public function __construct(ConnectionPool $connectionPool, WhereClauseBuilder $whereClauseBuilder)
    {
        $this->connectionPool = $connectionPool;
        $this->whereClauseBuilder = $whereClauseBuilder;
    }

    public function addRow($tableName, array $row)
    {
        $this->assertValidTableName($tableName);

        $connection = $this->getConnection($tableName);
        try {
            $connection->insert($tableName, $row);

            return (int)$connection->lastInsertId();
        } catch (DBALException $exception) {
            throw SqlErrorException::fromException($exception);
        }
    }

    public function updateRow($tableName, array $identifier, array $row)
    {
        $this->assertValidTableName($tableName);
        try {
            return $this->getConnection($tableName)->update($tableName, $row, $identifier);
        } catch (DBALException $exception) {
            throw SqlErrorException::fromException($exception);
        }
    }

    public function removeRow($tableName, array $identifier)
    {
        $this->assertValidTableName($tableName);
        try {
            return $this->getConnection($tableName)->delete($tableName, $identifier);
        } catch (DBALException $exception) {
            throw SqlErrorException::fromException($exception);
        }
    }

    public function getObjectCountByQuery($tableName, $query)
    {
        $this->assertValidTableName($tableName);

        $baseQuery = "SELECT COUNT(*) AS count FROM `$tableName`";
        if ($this->isQueryEmpty($query)) {
            try {
                $statement = $this->getConnection($tableName)->executeQuery($baseQuery);
            } catch (DBALException $exception) {
                throw SqlErrorException::fromException($exception);
            }
        } else {
            $this->whereClauseBuilder->build($query);
            $whereClause = $this->whereClauseBuilder->getWhere();

            try {
                $statement = $this->getConnection($tableName)->executeQuery(
                    $baseQuery . " WHERE " . $whereClause->getClause(),
                    $whereClause->getBoundVariables()
                );
            } catch (DBALException $exception) {
                throw SqlErrorException::fromException($exception);
            }
        }
        try {
            $result = $statement->fetch();
        } catch (DBALException $exception) {
            throw SqlErrorException::fromException($exception);
        }

        return $result['count'];
    }

    public function getObjectDataByQuery($tableName, $query)
    {
        $this->assertValidTableName($tableName);

        $baseSql = "SELECT * FROM `$tableName`";
        if ($this->isQueryEmpty($query)) {
            // TODO: Add support for ordering and pagination for Query objects without constraints
            if ($query instanceof QueryInterface
                && ($query->getLimit() || $query->getOffset() || $query->getOrderings())) {
                throw new \LogicException(
                    'Queries without constraints but pagination or orderings are not implemented'
                );
            }
            try {
                $statement = $this->getConnection($tableName)->executeQuery($baseSql);
            } catch (DBALException $exception) {
                throw SqlErrorException::fromException($exception);
            }
        } else {
            $this->whereClauseBuilder->build($query);
            $whereClause = $this->whereClauseBuilder->getWhere();

            $sql = $baseSql . " WHERE " . $whereClause->getClause();
            if ($query instanceof QueryInterface) {
                $sql = $this->addOrderingAndLimit($sql, $query);
            }

            try {
                $statement = $this->getConnection($tableName)->executeQuery($sql, $whereClause->getBoundVariables());
            } catch (DBALException $exception) {
                throw SqlErrorException::fromException($exception);
            }
        }

        try {
            return $statement->fetchAll();
        } catch (DBALException $exception) {
            throw SqlErrorException::fromException($exception);
        }
    }

    public function executeQuery($query)
    {
        try {
            return $this->getConnection('fe_users')->executeQuery($query);
        } catch (DBALException $exception) {
            throw SqlErrorException::fromException($exception);
        }
    }

    private function getConnection($table)
    {
        $this->assertValidTableName($table);

        return $this->connectionPool->getConnectionForTable($table);
    }

    private function addOrderingAndLimit($sql, QueryInterface $query)
    {
        $ordering = $this->createOrderingStatementFromQuery($query);
        if ($ordering) {
            $sql .= ' ORDER BY ' . $ordering;
        }

        $limit = $this->createLimitStatementFromQuery($query);
        if ($limit) {
            $sql .= ' LIMIT ' . $limit;
        }

        return $sql;
    }
}
