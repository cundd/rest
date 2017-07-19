<?php

/**
 * @author COD
 * Created 09.01.14 11:12
 */

$extensionPath = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('rest');
return array(
    'Cundd\\Rest\\Command\\RestCommandController' => $extensionPath . 'Classes/Cundd/Rest/Command/RestCommandController.php',
);
