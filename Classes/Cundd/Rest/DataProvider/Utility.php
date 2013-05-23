<?php
namespace Cundd\Rest\DataProvider;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * A utility class with static methods for Data Providers
 * @package Cundd\Rest\DataProvider
 */
class Utility {
	/**
	 * Returns an array of class name parts including vendor, extension
	 * and domain model
	 *
	 * Example:
	 *   array(
	 *     Vendor
	 *     MyExt
	 *     MyModel
	 *   )
	 * @param $path
	 * @return array
	 */
	static public function getClassNamePartsForPath($path) {
		if (strpos($path, '_') !== FALSE) {
			$path = GeneralUtility::underscoredToUpperCamelCase($path);
		}
		$parts = explode('-', $path);
		if (count($parts) < 3) {
			array_unshift($parts, '');
		}
		$parts = array_map(function($part) {return ucfirst($part);}, $parts);
		return $parts;
	}
}