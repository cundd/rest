<?php


namespace Cundd\Rest\Tests\Functional;


use Cundd\Rest\VirtualObject\Persistence\BackendFactory;
use Cundd\Rest\VirtualObject\Persistence\Exception\SqlErrorException;

trait FeUserCaseTrait
{
    /**
     * Change the `fe_users` table to include the `tx_rest_apikey` column
     *
     * @throws SqlErrorException
     */
    public static function addApiKeyColumn()
    {
        $databaseConnection = BackendFactory::getBackend();
        try {
            $databaseConnection->executeQuery('ALTER TABLE fe_users ADD tx_rest_apikey TINYTEXT;');
        } catch (SqlErrorException $exception) {
            $duplicateColumnErrorCode = 1060;
            if ($exception->getCode() != $duplicateColumnErrorCode) {
                throw $exception;
            }
        }
    }
}
