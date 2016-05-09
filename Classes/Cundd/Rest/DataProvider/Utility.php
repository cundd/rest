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

namespace Cundd\Rest\DataProvider;

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * A utility class with static methods for Data Providers
 *
 * @package Cundd\Rest\DataProvider
 */
class Utility {
    /**
     * Separator between vendor, extension and model in the API path
     */
    const API_PATH_PART_SEPARATOR = '-';

    /**
     * Mapping from singular to plural
     *
     * @var array
     */
    protected static $singularToPlural = array();

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
     *
     * @param string $path
     * @param bool $convertPlural Indicates if plural resource names should be converted
     * @return array
     */
    public static function getClassNamePartsForPath($path, $convertPlural = TRUE) {
        if (strpos($path, '_') !== FALSE) {
            $path = GeneralUtility::underscoredToUpperCamelCase($path);
        }
        $parts = explode(self::API_PATH_PART_SEPARATOR, $path);
        if (count($parts) < 3) {
            array_unshift($parts, '');
        }

        if ($convertPlural && $parts) {
            $lastPartIndex = count($parts) - 1;
            $parts[$lastPartIndex] = static::singularize($parts[$lastPartIndex]);
        }

        return array_map(function ($part) {
            return ucfirst($part);
        }, $parts);
    }

    /**
     * Tries to generate the API path for the given class name
     *
     * @param string $className
     * @return string|bool Returns the path or FALSE if it couldn't be determined
     */
    public static function getPathForClassName($className) {
        if (strpos($className, '\\') === FALSE) {
            if (substr($className, 0, 3) === 'Tx_') {
                $className = substr($className, 3);
            }
            $className = str_replace('_', '\\', $className);
        }

        $className = str_replace('\\Domain\\Model\\', '\\', $className);
        $classNameParts = array_map(
            array('TYPO3\\CMS\\Core\\Utility\\GeneralUtility', 'camelCaseToLowerCaseUnderscored'),
            explode('\\', $className)
        );

        return implode(self::API_PATH_PART_SEPARATOR, $classNameParts);
    }

    /**
     * Tries to convert an english plural into it's singular.
     *
     * @param string $word
     * @return string
     */
    public static function singularize($word) {
        $customMapping = array_search($word, static::$singularToPlural, true);
        if ($customMapping !== false) {
            return $customMapping;
        }
        $customMapping = array_search(strtolower($word), static::$singularToPlural, true);
        if ($customMapping !== false) {
            return $customMapping;
        }

        // Here is the list of rules. To add a scenario,
        // Add the plural ending as the key and the singular
        // ending as the value for that key. This could be
        // turned into a preg_replace and probably will be
        // eventually, but for now, this is what it is.
        //
        // Note: The first rule has a value of false since
        // we don't want to mess with words that end with
        // double 's'. We normally wouldn't have to create
        // rules for words we don't want to mess with, but
        // the last rule (s) would catch double (ss) words
        // if we didn't stop before it got to that rule.
        $rules = array(
            'ss'  => false,
            'os'  => 'o',
            'ies' => 'y',
            'xes' => 'x',
            'oes' => 'o',
            'ves' => 'f',
            's'   => '');
        // Loop through all the rules and do the replacement.
        foreach (array_keys($rules) as $key) {
            // If the end of the word doesn't match the key,
            // it's not a candidate for replacement. Move on
            // to the next plural ending.
            if (substr($word, (strlen($key) * -1)) != $key)
                continue;
            // If the value of the key is false, stop looping
            // and return the original version of the word.
            if ($key === false)
                return $word;
            // We've made it this far, so we can do the
            // replacement.
            return substr($word, 0, strlen($word) - strlen($key)) . $rules[$key];
        }
        return $word;
    }

    /**
     * Add a mapping from singular to plural
     *
     * @param $singular
     * @param $plural
     */
    public static function registerSingularForPlural($singular, $plural) {
        static::$singularToPlural[$singular] = $plural;
    }
}
