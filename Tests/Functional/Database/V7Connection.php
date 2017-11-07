<?php


namespace Cundd\Rest\Tests\Functional\Database;


use TYPO3\CMS\Core\Database\DatabaseConnection;

class V7Connection implements DatabaseConnectionInterface
{
    /**
     * @var DatabaseConnection
     */
    private $connection;

    /**
     * V7Connection constructor.
     *
     * @param $connection
     */
    public function __construct(DatabaseConnection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @inheritDoc
     */
    public function exec_SELECTgetRows(
        $select_fields,
        $from_table,
        $where_clause,
        $groupBy = '',
        $orderBy = '',
        $limit = '',
        $uidIndexField = ''
    ) {
        return $this->connection->exec_SELECTgetRows(
            $select_fields,
            $from_table,
            $where_clause,
            $groupBy,
            $orderBy,
            $limit,
            $uidIndexField
        );
    }

    public function exec_SELECTquery(
        $select_fields,
        $from_table,
        $where_clause,
        $groupBy = '',
        $orderBy = '',
        $limit = ''
    ) {
        return $this->connection->exec_SELECTquery(
            $select_fields,
            $from_table,
            $where_clause,
            $groupBy,
            $orderBy,
            $limit
        );
    }

    public function exec_UPDATEquery($table, $where, $fields_values, $no_quote_fields = false)
    {
        return $this->connection->exec_UPDATEquery($table, $where, $fields_values, $no_quote_fields);
    }

    public function exec_DELETEquery($table, $where)
    {
        return $this->connection->exec_DELETEquery($table, $where);
    }

    public function fullQuoteStr($str, $table, $allowNull = false)
    {
        return $this->connection->fullQuoteStr($str, $table, $allowNull);
    }

    public function exec_INSERTquery($table, $fields_values, $no_quote_fields = false)
    {
        return $this->connection->exec_INSERTquery($table, $fields_values, $no_quote_fields);
    }

    public function sql_query($query)
    {
        return $this->connection->sql_query($query);
    }

    public function sql_error()
    {
        return $this->connection->sql_error();
    }

    public function sql_errno()
    {
        return $this->connection->sql_errno();
    }

    public function sql_insert_id()
    {
        return $this->connection->sql_insert_id();
    }

    public function sql_fetch_row($res)
    {
        return $this->connection->sql_fetch_row($res);
    }

    public function __call($name, $arguments)
    {
        return call_user_func_array([$this->connection, $name], $arguments);
    }
}
