<?php
if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

$GLOBALS['TYPO3_CONF_VARS']['FE']['eID_include']['rest'] = \Cundd\Rest\BootstrapDispatcher::class . '::processRequest';

call_user_func(function () {
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
});

if (!is_array($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['cundd_rest_cache'])) {
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['cundd_rest_cache'] = array();
}
