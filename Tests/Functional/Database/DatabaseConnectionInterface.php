<?php


namespace Cundd\Rest\Tests\Functional\Database;


interface DatabaseConnectionInterface
{
    /**
     * Creates and executes a SELECT SQL-statement
     * Using this function specifically allow us to handle the LIMIT feature independently of DB.
     *
     * @param string $select_fields List of fields to select from the table. This is what comes right after "SELECT ...". Required value.
     * @param string $from_table    Table(s) from which to select. This is what comes right after "FROM ...". Required value.
     * @param string $where_clause  Additional WHERE clauses put in the end of the query. NOTICE: You must escape values in this argument with $this->fullQuoteStr() yourself! DO NOT PUT IN GROUP BY, ORDER BY or LIMIT!
     * @param string $groupBy       Optional GROUP BY field(s), if none, supply blank string.
     * @param string $orderBy       Optional ORDER BY field(s), if none, supply blank string.
     * @param string $limit         Optional LIMIT value ([begin,]max), if none, supply blank string.
     * @return bool|\mysqli_result|object MySQLi result object / DBAL object
     */
    public function exec_SELECTquery(
        $select_fields,
        $from_table,
        $where_clause,
        $groupBy = '',
        $orderBy = '',
        $limit = ''
    );

    /**
     * Creates and executes an INSERT SQL-statement for $table from the array with field/value pairs $fields_values.
     * Using this function specifically allows us to handle BLOB and CLOB fields depending on DB
     *
     * @param string            $table           Table name
     * @param array             $fields_values   Field values as key=>value pairs. Values will be escaped internally. Typically you would fill an array like "$insertFields" with 'fieldname'=>'value' and pass it to this function as argument.
     * @param bool|array|string $no_quote_fields See fullQuoteArray()
     * @return bool|\mysqli_result|object MySQLi result object / DBAL object
     */
    public function exec_INSERTquery($table, $fields_values, $no_quote_fields = false);

    /**
     * Creates and executes an UPDATE SQL-statement for $table where $where-clause (typ. 'uid=...') from the array with field/value pairs $fields_values.
     * Using this function specifically allow us to handle BLOB and CLOB fields depending on DB
     *
     * @param string            $table           Database tablename
     * @param string            $where           WHERE clause, eg. "uid=1". NOTICE: You must escape values in this argument with $this->fullQuoteStr() yourself!
     * @param array             $fields_values   Field values as key=>value pairs. Values will be escaped internally. Typically you would fill an array like "$updateFields" with 'fieldname'=>'value' and pass it to this function as argument.
     * @param bool|array|string $no_quote_fields See fullQuoteArray()
     * @return bool|\mysqli_result|object MySQLi result object / DBAL object
     */
    public function exec_UPDATEquery($table, $where, $fields_values, $no_quote_fields = false);

    /**
     * Creates and executes a DELETE SQL-statement for $table where $where-clause
     *
     * @param string $table Database tablename
     * @param string $where WHERE clause, eg. "uid=1". NOTICE: You must escape values in this argument with $this->fullQuoteStr() yourself!
     * @return bool|\mysqli_result|object MySQLi result object / DBAL object
     */
    public function exec_DELETEquery($table, $where);

    /**
     * Escaping and quoting values for SQL statements.
     *
     * @param string $str       Input string
     * @param string $table     Table name for which to quote string. Just enter the table that the field-value is selected from (and any DBAL will look up which handler to use and then how to quote the string!).
     * @param bool   $allowNull Whether to allow NULL values
     * @return string Output string; Wrapped in single quotes and quotes in the string (" / ') and \ will be backslashed (or otherwise based on DBAL handler)
     * @see quoteStr()
     */
    public function fullQuoteStr($str, $table, $allowNull = false);

    /**
     * Creates and executes a SELECT SQL-statement AND traverse result set and returns array with records in.
     *
     * @param string $select_fields List of fields to select from the table. This is what comes right after "SELECT ...". Required value.
     * @param string $from_table    Table(s) from which to select. This is what comes right after "FROM ...". Required value.
     * @param string $where_clause  Additional WHERE clauses put in the end of the query. NOTICE: You must escape values in this argument with $this->fullQuoteStr() yourself! DO NOT PUT IN GROUP BY, ORDER BY or LIMIT!
     * @param string $groupBy       Optional GROUP BY field(s), if none, supply blank string.
     * @param string $orderBy       Optional ORDER BY field(s), if none, supply blank string.
     * @param string $limit         Optional LIMIT value ([begin,]max), if none, supply blank string.
     * @param string $uidIndexField If set, the result array will carry this field names value as index. Requires that field to be selected of course!
     * @return array|NULL Array of rows, or NULL in case of SQL error
     * @see exec_SELECTquery()
     * @throws \InvalidArgumentException
     */
    public function exec_SELECTgetRows(
        $select_fields,
        $from_table,
        $where_clause,
        $groupBy = '',
        $orderBy = '',
        $limit = '',
        $uidIndexField = ''
    );

    /**
     * Executes query
     * MySQLi query() wrapper function
     * Beware: Use of this method should be avoided as it is experimentally supported by DBAL. You should consider
     * using exec_SELECTquery() and similar methods instead.
     *
     * @param string $query Query to execute
     * @return bool|\mysqli_result|object MySQLi result object / DBAL object
     */
    public function sql_query($query);

    /**
     * Returns the error status on the last query() execution
     *
     * @return string MySQLi error string.
     */
    public function sql_error();

    /**
     * Returns the error number on the last query() execution
     *
     * @return int MySQLi error number
     */
    public function sql_errno();

    /**
     * Get the ID generated from the previous INSERT operation
     *
     * @return int The uid of the last inserted record.
     */
    public function sql_insert_id();

    /**
     * Returns an array that corresponds to the fetched row, or FALSE if there are no more rows.
     * The array contains the values in numerical indices.
     * MySQLi fetch_row() wrapper function
     *
     * @param bool|\mysqli_result|\Doctrine\DBAL\Driver\Statement|object $res MySQLi result object / DBAL object
     * @return array|bool Array with result rows.
     */
    public function sql_fetch_row($res);
}
