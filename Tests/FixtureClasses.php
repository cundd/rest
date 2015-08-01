<?php
/*
 *  Copyright notice
 *
 *  (c) 2014 Daniel Corn <info@cundd.net>, cundd
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 */

namespace Cundd\Rest\Test\Core;

use Cundd\Rest\HandlerInterface;

class MyModel extends \TYPO3\CMS\Extbase\DomainObject\AbstractDomainObject {
    /**
     * @var string
     */
    protected $name = 'Initial value';

    /**
     * @param string $name
     */
    public function setName($name) {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName() {
        return $this->name;
    }
}

class MyModelRepository extends \TYPO3\CMS\Extbase\Persistence\Repository {
}

class MyNestedModel extends \TYPO3\CMS\Extbase\DomainObject\AbstractDomainObject {
    /**
     * @var string
     */
    protected $base = 'Base';

    /**
     * @var \DateTime
     */
    protected $date = NULL;

    /**
     * @var \Cundd\Rest\Test\Core\MyModel
     */
    protected $child = NULL;

    function __construct() {
        $this->child = new MyModel();
        $this->date = new \DateTime();
    }


    /**
     * @param string $base
     */
    public function setBase($base) {
        $this->base = $base;
    }

    /**
     * @return string
     */
    public function getBase() {
        return $this->base;
    }

    /**
     * @param \Cundd\Rest\Test\Core\MyModel $child
     */
    public function setChild($child) {
        $this->child = $child;
    }

    /**
     * @return \Cundd\Rest\Test\Core\MyModel
     */
    public function getChild() {
        return $this->child;
    }

    /**
     * @param \DateTime $date
     */
    public function setDate($date) {
        $this->date = $date;
    }

    /**
     * @return \DateTime
     */
    public function getDate() {
        return $this->date;
    }
}

class MyNestedJsonSerializeModel extends MyNestedModel {
    public function jsonSerialize() {
        return array(
            'base' => $this->base,
            'child' => $this->child
        );
    }
}

class MyHandler implements HandlerInterface {
    /**
     * @inheritDoc
     */
    public function setRequest($request) {
    }

    /**
     * @inheritDoc
     */
    public function getRequest() {
    }

    /**
     * @inheritDoc
     */
    public function configureApiPaths() {
    }
}

class_alias('Cundd\\Rest\\DataProvider\\DataProvider', 'Tx_MyExt_Rest_DataProvider');
class_alias('Cundd\\Rest\\DataProvider\\DataProvider', 'Vendor\\MySecondExt\\Rest\\DataProvider');
class_alias('Cundd\\Rest\\Test\\Core\\MyHandler', 'Tx_MyExt_Rest_Handler');
class_alias('Cundd\\Rest\\Test\\Core\\MyHandler', 'Vendor\\MySecondExt\\Rest\\Handler');
