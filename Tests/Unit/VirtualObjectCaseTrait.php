<?php

declare(strict_types=1);

namespace Cundd\Rest\Tests\Unit;

use Cundd\Rest\VirtualObject\Configuration;
use Cundd\Rest\VirtualObject\ConfigurationFactory;

trait VirtualObjectCaseTrait
{
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

        return new Configuration(
            ConfigurationFactory::preparePropertyMapping($testConfiguration['resource_type']['mapping'])
        );
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
        $this->testConfiguration = json_decode($this->getTestConfigurationJSONString(), true);

        return $this->testConfiguration;
    }

    /**
     * @return string
     */
    protected function getTestConfigurationJSONString(): string
    {
        return <<<CONFIGURATION
{
    "resource_type": {
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
    },
    "MyOtherResourceType": {
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
