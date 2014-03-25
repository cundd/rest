<?php
/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 24.03.14
 * Time: 12:27
 */

namespace Cundd\Rest\Test\VirtualObject;

\Tx_CunddComposer_Autoloader::register();
require_once __DIR__ . '/AbstractVirtualObjectCase.php';


use Cundd\Rest\VirtualObject\Configuration;
use TYPO3\CMS\Extbase\Tests\Unit\BaseTestCase;

/**
 * Abstract base class for Virtual Object tests with database support
 *
 * @package Cundd\Rest\Test\VirtualObject
 */
class AbstractDatabaseCase extends AbstractVirtualObjectCase {
	/**
	 * Test database name
	 *
	 * @var string
	 */
	static protected $testDatabaseTable = 'tx_rest_domain_model_test';

	/**
	 * Test data sets
	 *
	 * @var array
	 */
	static protected $testData = array(
		array(
			'uid'          => 100,
			'title'        => 'Test entry',
			'content'      => 'This is my text',
			'content_time' => 1395678480
		),
		array(
			'uid'          => 200,
			'title'        => 'Test entry',
			'content'      => 'This is my second text',
			'content_time' => 1395678480
		)
	);

	/**
	 * @var array
	 */
	protected $testConfiguration = array();

	/**
	 * Returns the test configuration object
	 * @return Configuration
	 */
	protected function getTestConfiguration() {
		$testConfiguration = $this->getTestConfigurationData();
		return new \Cundd\Rest\VirtualObject\Configuration($testConfiguration['ResourceName']['mapping']);
	}

	/**
	 * Returns the configuration data
	 *
	 * @return array
	 */
	protected function getTestConfigurationData() {
		if ($this->testConfiguration) {
			return $this->testConfiguration;
		}

		$testDatabaseTable = self::$testDatabaseTable;

		$testConfigurationJson = <<<CONFIGURATION
{
    "ResourceName": {
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

		$this->testConfiguration = json_decode($testConfigurationJson, TRUE);
		return $this->testConfiguration;
	}


	public function setUp() {
		self::insertData();

		parent::setUp();
	}

	public function tearDown() {
		self::truncateTable();

		parent::tearDown();
	}

	public static function setUpBeforeClass() {
		self::dropTable();
		self::createTable();
	}

	public static function tearDownAfterClass() {
		#	self::dropTable();
	}


	static protected function createTable() {
		$testDatabaseTable = self::$testDatabaseTable;
		$createTableSQL    = <<<SQL
CREATE TABLE $testDatabaseTable (

	uid int(11) NOT NULL AUTO_INCREMENT,

	title varchar(255) DEFAULT '' NOT NULL,
	content text NOT NULL,

	content_time int(11),

	PRIMARY KEY (uid)
) AUTO_INCREMENT=1;
SQL;

		/** @var \TYPO3\CMS\Core\Database\DatabaseConnection $databaseConnection */
		$databaseConnection = $GLOBALS['TYPO3_DB'];
		$databaseConnection->sql_query($createTableSQL);
	}

	static protected function dropTable() {
		$testDatabaseTable = self::$testDatabaseTable;
		$dropTableSQL      = <<<SQL
		DROP TABLE IF EXISTS $testDatabaseTable;
SQL;
		/** @var \TYPO3\CMS\Core\Database\DatabaseConnection $databaseConnection */
		$databaseConnection = $GLOBALS['TYPO3_DB'];
		$databaseConnection->sql_query($dropTableSQL);
	}

	static protected function truncateTable() {
		$testDatabaseTable = self::$testDatabaseTable;
		$dropTableSQL      = <<<SQL
		TRUNCATE TABLE  $testDatabaseTable

SQL;
		/** @var \TYPO3\CMS\Core\Database\DatabaseConnection $databaseConnection */
		$databaseConnection = $GLOBALS['TYPO3_DB'];
		$databaseConnection->sql_query($dropTableSQL);
	}

	static protected function insertData() {
		/** @var \TYPO3\CMS\Core\Database\DatabaseConnection $databaseConnection */
		$databaseConnection = $GLOBALS['TYPO3_DB'];
		$databaseConnection->exec_INSERTquery(self::$testDatabaseTable, self::$testData[0]);
		$databaseConnection->exec_INSERTquery(self::$testDatabaseTable, self::$testData[1]);
	}
} 