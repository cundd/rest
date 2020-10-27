<?php

use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Utility\GeneralUtility;

if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

call_user_func(
    function () {
        // Register eID
        $GLOBALS['TYPO3_CONF_VARS']['FE']['eID_include']['rest'] = \Cundd\Rest\BootstrapDispatcher::class . '::processRequest';

        if (isset($_SERVER['REQUEST_URI'])
            && class_exists(Typo3Version::class)
            && (new Typo3Version())->getMajorVersion() < 10) {
            // Detect and "hijack" REST requests
            $restRequestBasePath = (string)(getenv(
                'TYPO3_REST_REQUEST_BASE_PATH'
            ) ?: getenv(
                'REDIRECT_TYPO3_REST_REQUEST_BASE_PATH'
            ));

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

        $implementationsMap = [
            \Cundd\Rest\Configuration\ConfigurationProviderInterface::class => \Cundd\Rest\Configuration\TypoScriptConfigurationProvider::class,
            \Cundd\Rest\Authentication\UserProviderInterface::class         => \Cundd\Rest\Authentication\UserProvider\FeUserProvider::class,
            \Cundd\Rest\Handler\HandlerInterface::class                     => \Cundd\Rest\Handler\CrudHandler::class,
        ];
        $objectContainer = GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Object\Container\Container::class);
        foreach ($implementationsMap as $interface => $impl) {
            $objectContainer->registerImplementation($interface, $impl);
        }
    }
);
