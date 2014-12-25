<?php
/*
 *  Copyright notice
 *
 *  (c) 2014 Daniel Corn <info@cundd.net>, cundd
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 */

/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 24.03.14
 * Time: 12:27
 */

namespace Cundd\Rest\Test\VirtualObject;

use Cundd\Rest\Test\AbstractCase;
use Cundd\Rest\VirtualObject\Configuration;

require_once __DIR__ . '/../AbstractCase.php';

/**
 * Abstract base class for Virtual Object tests
 *
 * @package Cundd\Rest\Test\VirtualObject
 */
class AbstractVirtualObjectCase extends AbstractCase {
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
		return new \Cundd\Rest\VirtualObject\Configuration(
			\Cundd\Rest\VirtualObject\ConfigurationFactory::preparePropertyMapping($testConfiguration['ResourceName']['mapping'])
		);
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
		$this->testConfiguration = json_decode($this->getTestConfigurationJSONString(), TRUE);
		return $this->testConfiguration;
	}

	/**
	 * @return string
	 */
	protected function getTestConfigurationJSONString() {
		return <<<CONFIGURATION
{
    "ResourceName": {
        "mapping": {
        	"identifier": "property1",
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
                },
                "property_seven": {
                    "type": "boolean",
                    "column": "property_seven"
                },
                "property_eight": "boolean"
            }
        }
    }
}
CONFIGURATION;
	}
}
