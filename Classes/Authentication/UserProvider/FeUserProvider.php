<?php

namespace Cundd\Rest\Authentication\UserProvider;

use Cundd\Rest\Authentication\UserProviderInterface;

/**
 * User Provider implementation for FeUsers
 */
class FeUserProvider implements UserProviderInterface
{
    /**
     * Name of the password column
     */
    const PASSWORD_COLUMN_NAME = 'tx_rest_apikey';

    /**
     * Returns if the user with the given credentials is valid
     *
     * @param string $username
     * @param string $password
     * @return boolean
     */
    public function checkCredentials($username, $password)
    {
        $databaseAdapter = $this->getDatabaseAdapter();

        $whereClause = $this->buildWhereStatement(
            [
                'username' => $username,
                'password' => $password,
            ]
        );
        $result = $databaseAdapter->exec_SELECTquery('COUNT(*)', 'fe_users', $whereClause);
        $row = $databaseAdapter->sql_fetch_row($result);

        return (bool)$row[0];
    }

    /**
     * Builds the where statement from the given properties
     *
     * @param array $properties
     * @return string
     */
    protected function buildWhereStatement($properties)
    {
        $whereParts = [];
        $databaseAdapter = $this->getDatabaseAdapter();
        foreach ($properties as $key => $value) {
            if ($key === 'password') {
                list($value, $key) = $this->preparePassword($value);
            }
            if (is_int($value)) {
                $whereParts[] = '`' . $key . '`=' . $value;
            } else {
                $whereParts[] = '`' . $key . '`=' . $databaseAdapter->fullQuoteStr($value, 'fe_users');
            }
        }
        $whereParts[] = '`' . self::PASSWORD_COLUMN_NAME . '`<>\'\'';
        $whereParts[] = '(`disable`=0)';
        $whereParts[] = '(`deleted`=0)';
        $whereParts[] = sprintf('(`starttime`<=%d)', time());
        $whereParts[] = sprintf('(endtime=0 OR endtime>%d)', time());

        return implode(' AND ', $whereParts);
    }

    /**
     * Returns an array containing the prepared password and table column
     *
     * @param string $password
     * @return array
     */
    protected function preparePassword($password)
    {
        return [$password, self::PASSWORD_COLUMN_NAME];
    }

    /**
     * Returns the database adapter
     *
     * @return \TYPO3\CMS\Core\Database\DatabaseConnection
     */
    protected function getDatabaseAdapter()
    {
        return $GLOBALS['TYPO3_DB'];
    }
}
