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

namespace Cundd\Rest\VirtualObject;

/**
 * Class VirtualObject
 *
 * A simple wrapper object for data
 *
 * @package Cundd\Rest
 */
class VirtualObject {
	/**
	 * The data
	 *
	 * @var array
	 */
	protected $data = array();

	function __construct($data = array()) {
		$this->data = $data;
	}

	/**
	 * Sets the data
	 *
	 * @param array $data
	 * @return $this
	 */
	public function setData($data) {
		$this->data = $data;
		return $this;
	}

	/**
	 * Returns the data
	 *
	 * @return array
	 */
	public function getData() {
		return $this->data;
	}

	/**
	 * Returns the value for the given key
	 *
	 * @param string $key
	 * @return mixed
	 */
	public function valueForKey($key) {
		return isset($this->data[$key]) ? $this->data[$key] : NULL;
	}

	/**
	 * Sets the value for the given key
	 *
	 * @param string $key
	 * @param mixed  $value
	 * @return $this
	 */
	public function setValueForKey($key, $value) {
		$this->data[$key] = $value;
		return $this;
	}

	/**
	 * Return the data if transformed to JSON
	 *
	 * @return array
	 */
	public function jsonSerialize() {
		return $this->getData();
	}
}
