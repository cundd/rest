<?php

namespace Cundd\Rest;


if (interface_exists(\TYPO3\CMS\Core\SingletonInterface::class)) {
    interface SingletonInterface extends \TYPO3\CMS\Core\SingletonInterface
    {
    }
} else {
    interface SingletonInterface
    {
    }
}
