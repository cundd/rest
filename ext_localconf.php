<?php

use Cundd\Rest\Authentication\UserProvider\FeUserProvider;
use Cundd\Rest\Authentication\UserProviderInterface;
use Cundd\Rest\BootstrapDispatcher;
use Cundd\Rest\Configuration\ConfigurationProviderInterface;
use Cundd\Rest\Configuration\TypoScriptConfigurationProvider;
use Cundd\Rest\Handler\CrudHandler;
use Cundd\Rest\Handler\HandlerInterface;
use TYPO3\CMS\Extbase\Object\Container\Container;

if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

call_user_func(
    function () {
        // TYPO3 v8
        if (!class_exists(TYPO3\CMS\Core\Information\Typo3Version::class)) {
            // Register eID
            $GLOBALS['TYPO3_CONF_VARS']['FE']['eID_include']['rest'] = BootstrapDispatcher::class . '::processRequest';

            if (isset($_SERVER['REQUEST_URI'])) {
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
        }

        // Register Cache
        if (!isset($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['cundd_rest_cache'])
            || !is_array($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['cundd_rest_cache'])) {
            $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['cundd_rest_cache'] = [];
        }

        $implementationsMap = [
            ConfigurationProviderInterface::class => TypoScriptConfigurationProvider::class,
            UserProviderInterface::class         => FeUserProvider::class,
            HandlerInterface::class                     => CrudHandler::class,
        ];
        $objectContainer = TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
            Container::class
        );
        foreach ($implementationsMap as $interface => $impl) {
            $objectContainer->registerImplementation($interface, $impl);
        }
    }
);
