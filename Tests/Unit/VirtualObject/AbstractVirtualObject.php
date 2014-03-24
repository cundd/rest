<?php
/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 24.03.14
 * Time: 12:27
 */

namespace Cundd\Rest\Test\VirtualObject;

\Tx_CunddComposer_Autoloader::register();

use Cundd\Rest\VirtualObject\Configuration;
use TYPO3\CMS\Extbase\Tests\Unit\BaseTestCase;

/**
 * Abstract base class for Virtual Object tests
 *
 * @package Cundd\Rest\Test\VirtualObject
 */
class AbstractVirtualObject extends BaseTestCase {
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
		$testConfigurationJson = <<<CONFIGURATION
{
    "ResourceName": {
        "mapping": {
            "tableName": "my_resource_table",

            "properties": {
                "property1": {
                    "type": "string",
                    "column": "property_one"
                },
                "property2": {
                    "type": "float",
                    "column": "property_two"
                },
                "property3": {
                    "type": "int",
                    "column": "property_three"
                },
                "property4": {
                    "type": "integer",
                    "column": "property_four"
                },
                "property5": {
                    "type": "bool",
                    "column": "property_five"
                },
                "property6": {
                    "type": "boolean",
                    "column": "property_six"
                }
            }
        }
    }
}
CONFIGURATION;

		$this->testConfiguration = json_decode($testConfigurationJson, TRUE);
		return $this->testConfiguration;
	}
} 