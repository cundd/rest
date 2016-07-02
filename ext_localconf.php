<?php
if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

$GLOBALS['TYPO3_CONF_VARS']['FE']['eID_include']['rest'] = 'EXT:rest/index.php';

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

if (TYPO3_MODE === 'BE') {
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['extbase']['commandControllers'][] = 'Cundd\\Rest\\Command\\RestCommandController';
}
