<?php
if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

call_user_func(
    function () {
        // Register eID
        $GLOBALS['TYPO3_CONF_VARS']['FE']['eID_include']['rest'] = \Cundd\Rest\BootstrapDispatcher::class . '::processRequest';

        // Detect and "hijack" REST requests
        if (isset($_SERVER['REQUEST_URI'])) {
            $restRequestBasePath = (string)getenv('TYPO3_REST_REQUEST_BASE_PATH');

            if ($restRequestBasePath) {
                $restRequestBasePath = '/' . trim($restRequestBasePath, '/');
            }

            $restRequestPrefix = $restRequestBasePath . '/rest/';
            $restRequestPrefixLength = strlen($restRequestPrefix);
            $requestUri = $_SERVER['REQUEST_URI'];

            if (substr($requestUri, 0, $restRequestPrefixLength) === $restRequestPrefix) {
                $_GET['eID'] = 'rest';
            }
        }

        // Register Cache
        if (!is_array($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['cundd_rest_cache'])) {
            $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['cundd_rest_cache'] = [];
        }

        // Register implementation classes
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\Cundd\Rest\Configuration\ConfigurationProviderInterface::class] = [
            'className' => \Cundd\Rest\Configuration\TypoScriptConfigurationProvider::class,
        ];
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\Cundd\Rest\Authentication\UserProviderInterface::class] = [
            'className' => \Cundd\Rest\Authentication\UserProvider\FeUserProvider::class,
        ];
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][Cundd\Rest\Handler\HandlerInterface::class] = [
            'className' => \Cundd\Rest\Handler\CrudHandler::class,
        ];
    }
);
