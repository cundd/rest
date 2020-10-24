# Use TYPO3 REST for your Extbase API

As described in the [Tutorial](https://rest.cundd.net/Tutorial/), the TYPO3 rest extension can be used as a boilerplate for your own Web Services. If you have a extbase Plugin you want to use, it can be called easily through a custom handler. To make things more convenient, a small helper function in your Handler is useful:

```php
protected function callExtbasePlugin($pluginName, $vendorName, $extensionName, $controllerName, $actionName, $arguments) { ... }
```
File: Handler.php

Before using this function, some things have to be mentioned:
 * Every plugin has to be configured in ext_localconf.php using [ExtensionUtility::configurePlugin](http://api.typo3.org/typo3cms/master/html/class_t_y_p_o3_1_1_c_m_s_1_1_extbase_1_1_utility_1_1_extension_utility.html) of TYPO3 Core API
 * Since the request and the sent data does not come from a fluid form, your controller should take care of the property mapping and type converting of the arguments. (non-scalar argument variables)
 * Extbase's bootstrap accepts only a string as return value, the json rendering of the output has to be done in your controller (you may want to have a look at [JsonView](http://api.typo3.org/typo3cms/master/html/class_t_y_p_o3_1_1_c_m_s_1_1_install_1_1_view_1_1_json_view.html) class of extbase or [this Tutorial](https://usetypo3.com/json-view.html) )

## Example

Lets say we want to create specific domain objects with a custom validation using a web service api.

Things needed:

* a Controller with the specific action method
* the (custom) validator for your model (if extbase finds a validator named \YourVendor\YourExt\Domain\Validator\YourModelValidator, it will validate against it, otherwise the validator has to be configured explicitely with a '@validate' annotation (see [Validating Domain Objects](https://docs.typo3.org/typo3cms/ExtbaseFluidBook/9-CrosscuttingConcerns/2-validating-domain-objects.html)) 
* configure you plugin in the ext_localconf.php File
* call it in your custom Handler

This fork has the proper adjustments to show how to do it. (untested)
