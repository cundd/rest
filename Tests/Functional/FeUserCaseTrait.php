<?php


namespace Cundd\Rest\Tests\Functional;


use Cundd\Rest\VirtualObject\Persistence\BackendFactory;
use Cundd\Rest\VirtualObject\Persistence\Exception\SqlErrorException;
use Doctrine\DBAL\Exception\NonUniqueFieldNameException;

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
            if ($exception->getPrevious() instanceof NonUniqueFieldNameException) {
                return;
            }
            $duplicateColumnErrorCode = 1060;
            if ($exception->getCode() == $duplicateColumnErrorCode) {
                return;
            }
            throw $exception;
        }
    }
}
