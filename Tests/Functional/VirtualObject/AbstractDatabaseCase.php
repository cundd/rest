<?php
declare(strict_types=1);

namespace Cundd\Rest\Tests\Functional\VirtualObject;

require_once __DIR__ . '/AbstractVirtualObjectCase.php';

use Cundd\Rest\VirtualObject\Configuration;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use function strpos;

/**
 * Abstract base class for Virtual Object tests with database support
 */
class AbstractDatabaseCase extends AbstractVirtualObjectCase
{
    /**
     * Test database name
     *
     * @var string
     */
    protected static $testDatabaseTable = 'tx_rest_domain_model_test';

    /**
     * Test data sets
     *
     * @var array
     */
    protected static $testData = [
        [
            'uid'          => 100,
            'title'        => 'Test entry',
            'content'      => 'This is my text',
            'content_time' => 1395678480,
        ],
        [
            'uid'          => 200,
            'title'        => 'Test entry',
            'content'      => 'This is my second text',
            'content_time' => 1395678480,
        ],
    ];

    /**
     * @var array
     */
    protected $testConfiguration = [];

    /**
     * Returns the test configuration object
     *
     * @return Configuration
     */
    protected function getTestConfiguration(): Configuration
    {
        $testConfiguration = $this->getTestConfigurationData();

        return new Configuration($testConfiguration['ResourceType']['mapping']);
    }

    /**
     * Returns the configuration data
     *
     * @return array
     */
    protected function getTestConfigurationData(): array
    {
        if ($this->testConfiguration) {
            return $this->testConfiguration;
        }

        $testDatabaseTable = self::$testDatabaseTable;

        $testConfigurationJson = <<<CONFIGURATION
{
    "ResourceType": {
        "mapping": {
        	"identifier": "uid",
            "tableName": "$testDatabaseTable",

            "properties": {
                "uid": {
                    "type": "int",
                    "column": "uid"
                },
                "title": {
                    "type": "string",
                    "column": "title"
                },
                "content": {
                    "type": "string",
                    "column": "content"
                },
                "contentTime": {
                    "type": "int",
                    "column": "content_time"
                }
            }
        }
    }
}
CONFIGURATION;

        $this->testConfiguration = json_decode($testConfigurationJson, true);

        return $this->testConfiguration;
    }

    public function setUp(): void
    {
        parent::setUp();
        $this->createTable();
        $this->insertData();
    }

    public function tearDown(): void
    {
        $this->truncateTable();
        $this->dropTable();
        parent::tearDown();
    }

    protected function createTable()
    {
        $testDatabaseTable = self::$testDatabaseTable;

        if ($this->isSqliteDriver()) {
            $createTableSQL = <<<SQL
CREATE TABLE $testDatabaseTable (

	uid INTEGER PRIMARY KEY AUTOINCREMENT,

	title varchar(255) DEFAULT '' NOT NULL,
	content text NOT NULL,

	content_time int(11)
);
SQL;
        } else {
            $createTableSQL = <<<SQL
CREATE TABLE $testDatabaseTable (

	uid int(11) NOT NULL AUTO_INCREMENT,

	title varchar(255) DEFAULT '' NOT NULL,
	content text NOT NULL,

	content_time int(11),

	PRIMARY KEY (uid)
) AUTO_INCREMENT=1;
SQL;
        }

        $databaseConnection = $this->getDatabaseBackend();
        $databaseConnection->executeQuery($createTableSQL);
    }

    protected function dropTable()
    {
        $testDatabaseTable = self::$testDatabaseTable;
        $dropTableSQL = <<<SQL
		DROP TABLE IF EXISTS $testDatabaseTable;
SQL;
        $databaseConnection = $this->getDatabaseBackend();
        $databaseConnection->executeQuery($dropTableSQL);
    }

    protected function truncateTable()
    {
        $testDatabaseTable = self::$testDatabaseTable;
        if ($this->isSqliteDriver()) {
            $dropTableSQL = "DELETE FROM $testDatabaseTable";
        } else {
            $dropTableSQL = "TRUNCATE TABLE $testDatabaseTable";
        }
        $databaseConnection = $this->getDatabaseBackend();
        $databaseConnection->executeQuery($dropTableSQL);
    }

    protected function insertData()
    {
        $databaseConnection = $this->getDatabaseBackend();
        $databaseConnection->addRow(self::$testDatabaseTable, self::$testData[0]);
        $databaseConnection->addRow(self::$testDatabaseTable, self::$testData[1]);
    }

    /**
     * @return bool
     */
    protected function isSqliteDriver(): bool
    {
        /** @var ConnectionPool $connectionPool */
        $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
        $connection = $connectionPool->getConnectionByName(ConnectionPool::DEFAULT_CONNECTION_NAME);

        return false !== strpos($connection->getDatabasePlatform()->getName(), 'sqlite');
    }
}
