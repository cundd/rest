<?php

defined('TYPO3') or die();

(static function (): void {
    // Register Cache
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['cundd_rest_cache'] ??= [];
})();
