<?php
/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 24.03.14
 * Time: 12:22
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
	 * The virtual data
	 *
	 * @var array
	 */
	protected $data = array();

	function __construct($data = array()) {
		$this->data = $data;
	}

	/**
	 * Sets the virtual data
	 *
	 * @param array $data
	 * @return $this
	 */
	public function setData($data) {
		$this->data = $data;
		return $this;
	}

	/**
	 * Returns the virtual data
	 *
	 * @return array
	 */
	public function getData() {
		return $this->data;
	}
} 