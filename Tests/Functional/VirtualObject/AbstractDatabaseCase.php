<?php


namespace Cundd\Rest\Tests\Functional\VirtualObject;

require_once __DIR__ . '/AbstractVirtualObjectCase.php';


use Cundd\Rest\VirtualObject\Configuration;

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
    protected function getTestConfiguration()
    {
        $testConfiguration = $this->getTestConfigurationData();

        return new Configuration($testConfiguration['ResourceType']['mapping']);
    }

    /**
     * Returns the configuration data
     *
     * @return array
     */
    protected function getTestConfigurationData()
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


    public function setUp()
    {
        parent::setUp();
        $this->createTable();
        $this->insertData();
    }

    public function tearDown()
    {
        $this->truncateTable();
        $this->dropTable();
        parent::tearDown();
    }

    protected function createTable()
    {
        $testDatabaseTable = self::$testDatabaseTable;
        $createTableSQL = <<<SQL
CREATE TABLE $testDatabaseTable (

	uid int(11) NOT NULL AUTO_INCREMENT,

	title varchar(255) DEFAULT '' NOT NULL,
	content text NOT NULL,

	content_time int(11),

	PRIMARY KEY (uid)
) AUTO_INCREMENT=1;
SQL;

        /** @var \TYPO3\CMS\Core\Database\DatabaseConnection $databaseConnection */
        $databaseConnection = $this->getDatabaseConnection();
        $databaseConnection->sql_query($createTableSQL);
    }

    protected function dropTable()
    {
        $testDatabaseTable = self::$testDatabaseTable;
        $dropTableSQL = <<<SQL
		DROP TABLE IF EXISTS $testDatabaseTable;
SQL;
        /** @var \TYPO3\CMS\Core\Database\DatabaseConnection $databaseConnection */
        $databaseConnection = $this->getDatabaseConnection();
        $databaseConnection->sql_query($dropTableSQL);
    }

    protected function truncateTable()
    {
        $testDatabaseTable = self::$testDatabaseTable;
        $dropTableSQL = <<<SQL
		TRUNCATE TABLE  $testDatabaseTable

SQL;
        /** @var \TYPO3\CMS\Core\Database\DatabaseConnection $databaseConnection */
        $databaseConnection = $this->getDatabaseConnection();
        $databaseConnection->sql_query($dropTableSQL);
    }

    protected function insertData()
    {
        /** @var \TYPO3\CMS\Core\Database\DatabaseConnection $databaseConnection */
        $databaseConnection = $this->getDatabaseConnection();
        $databaseConnection->exec_INSERTquery(self::$testDatabaseTable, self::$testData[0]);
        $databaseConnection->exec_INSERTquery(self::$testDatabaseTable, self::$testData[1]);
    }
}
