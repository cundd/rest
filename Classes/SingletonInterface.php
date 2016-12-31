<?php
/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 27.12.16
 * Time: 16:38
 */

namespace Cundd\Rest;


if (interface_exists(\TYPO3\CMS\Core\SingletonInterface::class)) {
    interface SingletonInterface extends \TYPO3\CMS\Core\SingletonInterface
    {
    }
} else {
    interface SingletonInterface {}
}