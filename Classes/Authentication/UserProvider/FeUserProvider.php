<?php

declare(strict_types=1);

namespace Cundd\Rest\Authentication\UserProvider;

use Cundd\Rest\Authentication\UserProviderInterface;
use Cundd\Rest\VirtualObject\Persistence\BackendFactory;
use Cundd\Rest\VirtualObject\Persistence\Query;
use Cundd\Rest\VirtualObject\Persistence\QueryInterface;

/**
 * User Provider implementation for FeUsers
 */
class FeUserProvider implements UserProviderInterface
{
    /**
     * Name of the password column
     */
    public const PASSWORD_COLUMN_NAME = 'tx_rest_apikey';

    /**
     * Returns if the user with the given credentials is valid
     *
     * @param string $username
     * @param string $password
     * @return boolean
     */
    public function checkCredentials(string $username, string $password): bool
    {
        if ('' === $username || '' === $password) {
            return false;
        }

        $backend = BackendFactory::getBackend();
        $query = [
            'username'                 => $username,
            self::PASSWORD_COLUMN_NAME => $password,
            'disable'                  => 0,
            'deleted'                  => 0,
            'starttime'                => [
                'value'    => time(),
                'operator' => QueryInterface::OPERATOR_LESS_THAN_OR_EQUAL_TO,
            ],
        ];

        $endtimeZero = [
            'endtime' => 0,
        ];
        $endtimeGtNow = [
            'endtime' => [
                'value'    => time(),
                'operator' => QueryInterface::OPERATOR_GREATER_THAN,
            ],
        ];

        return 0 < $backend->getObjectCountByQuery('fe_users', new Query(array_merge($query, $endtimeZero)))
            || 0 < $backend->getObjectCountByQuery('fe_users', new Query(array_merge($query, $endtimeGtNow)));
    }
}
