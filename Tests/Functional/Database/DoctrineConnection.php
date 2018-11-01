<?php


namespace Cundd\Rest\Tests\Functional\Database;


use Doctrine\DBAL\Statement;
use TYPO3\CMS\Core\Database\ConnectionPool;

class DoctrineConnection implements DatabaseConnectionInterface
{
    const DEFAULT_TABLE = 'pages';
    private $connection;
    private $lastUsedTable = '';

    /**
     * DoctrineConnection constructor.
     *
     * @param $connection
     */
    public function __construct(ConnectionPool $connection)
    {
        $this->connection = $connection;
    }

    public function exec_SELECTgetRows(
        $select_fields,
        $from_table,
        $where_clause,
        $groupBy = '',
        $orderBy = '',
        $limit = '',
        $uidIndexField = ''
    ) {
        if ($uidIndexField) {
            throw new \RuntimeException('Support for "uidIndexField" is not implemented');
        }
        /** @var Statement $statement */
        $statement = $this->exec_SELECTquery($select_fields, $from_table, $where_clause, $groupBy, $orderBy, $limit);

        return $statement->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function exec_SELECTquery(
        $select_fields,
        $from_table,
        $where_clause,
        $groupBy = '',
        $orderBy = '',
        $limit = ''
    ) {
        $query = 'SELECT ' . $select_fields . ' FROM ' . $from_table . ((string)$where_clause !== '' ? ' WHERE ' . $where_clause : '');
        $query .= (string)$groupBy !== '' ? ' GROUP BY ' . $groupBy : '';
        $query .= (string)$orderBy !== '' ? ' ORDER BY ' . $orderBy : '';
        $query .= (string)$limit !== '' ? ' LIMIT ' . $limit : '';

        return $this->sql_query($query);
    }

    public function sql_query($query)
    {
        try {
            return $this->getLastUsedConnection()->query($query);
        } catch (\Exception $exception) {
            return null;
        }
    }

    /**
     * @return \TYPO3\CMS\Core\Database\Connection
     */
    private function getLastUsedConnection()
    {
        return $this->connection->getConnectionForTable($this->lastUsedTable ?: self::DEFAULT_TABLE);
    }

    public function exec_INSERTquery($table, $fields_values, $no_quote_fields = false)
    {
        $this->lastUsedTable = $table;

        return $this->connection->getConnectionForTable($table)->insert($table, $fields_values);
    }

    public function exec_UPDATEquery($table, $where, $fields_values, $no_quote_fields = false)
    {
        return $this->connection->getConnectionForTable($table)->update(
            $table,
            $fields_values,
            $this->splitWhereString($where)
        );
    }

    /**
     * @param $where
     * @return array
     */
    private function splitWhereString($where)
    {
        $whereMap = [];
        $wherePairs = explode(' AND ', $where);
        foreach ($wherePairs as $pair) {
            list($column, $value) = explode('=', $pair);
            $whereMap[$column] = trim($value, '\'');
        }

        return $whereMap;
    }

    public function exec_DELETEquery($table, $where)
    {
        return $this->connection->getConnectionForTable($table)->delete($table, $this->splitWhereString($where));
    }

    public function sql_error()
    {
        $errorInfo = $this->getLastUsedConnection()->errorInfo();
        if (is_array($errorInfo)) {
            return (string)reset($errorInfo);
        }

        return (string)$errorInfo;
    }

    public function sql_errno()
    {
        return $this->getLastUsedConnection()->errorCode();
    }

    public function sql_insert_id()
    {
        return $this->connection->getConnectionForTable($this->lastUsedTable)->lastInsertId($this->lastUsedTable);
    }

    public function fullQuoteStr($str, $table, $allowNull = false)
    {
        return '\'' . str_replace('\'', '\\\'', $str) . '\'';
    }

    public function sql_fetch_row($res)
    {
        if (!$res) {
            return false;
        }

        $res->execute();

        return array_values($res->fetch());
    }
}
