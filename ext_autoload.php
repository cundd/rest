<?php

$extensionPath = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('rest');
return [
    'Cundd\\Rest\\Command\\RestCommandController' => $extensionPath . 'Classes/Cundd/Rest/Command/RestCommandController.php',
];
