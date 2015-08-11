<?php
/*
 *  Copyright notice
 *
 *  (c) 2014 Andreas Thurnheer-Meier <tma@iresults.li>, iresults
 *  Daniel Corn <cod@iresults.li>, iresults
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

/**
 * @author COD
 * Created 16.07.14 17:26
 */


namespace Cundd\Rest\Utility;

use TYPO3\CMS\Core\Log\LogLevel;

/**
 * A simple profiling utility
 *
 * @package Cundd\Rest\Utility
 */
class Profiler {
    /**
     * Start time
     *
     * @var float
     */
    protected $startTime = 0.0;

    /**
     * Additional options
     *
     * @var array
     */
    protected $options = array();

    /**
     * Data of the last run
     *
     * @var array
     */
    protected $profilingData = array();

    /**
     * @var resource|object
     */
    protected $outputHandler;

    /**
     * @param resource|object $outputHandler Output handler to use
     */
    function __construct($outputHandler = STDOUT) {
        $this->outputHandler = $outputHandler;
    }

    /**
     * Starts the profiling
     *
     * @param array|string $options Additional options to pass to the profiler. If a string is given it will be used as name
     * @return $this
     */
    public function start($options = array()) {
        if (is_string($options)) {
            $options = array('name' => $options);
        }
        $this->options = $options;
        $this->profilingData = array();
        $this->startTime = microtime(TRUE);
        return $this;
    }

    /**
     * Collects and returns profiling data of the current run
     *
     * @return array
     */
    public function collect() {
        $runEndTime = microtime(TRUE);
        $runId = count($this->profilingData);
        $lastRunData = end($this->profilingData);
        $runStartTime = $lastRunData ? $lastRunData['collectTime'] : $this->startTime;

        $currentRunData = array(
            'runId' => $runId,
            'name' => isset($this->options['name']) ? $this->options['name'] : 'unnamed',

            'startTime' => $this->startTime,
            'duration' => $runEndTime - $this->startTime,

            'runStartTime' => $runStartTime,
            'runEndTime' => $runEndTime,
            'runDuration' => $runEndTime - $runStartTime,
            'memory' => memory_get_usage(TRUE),
            'memoryPeak' => memory_get_peak_usage(TRUE),
        );

        if (isset($this->options['collectCaller']) && $this->options['collectCaller']) {
            $currentRunData['caller'] = $this->getCaller();
        }
        $this->profilingData[$runId] = $currentRunData;
        return $currentRunData;
    }

    /**
     * Outputs a profiling message using the output handler
     *
     * @return string Returns the message
     */
    public function output() {
        $messageParts = array();

        foreach ($this->profilingData as $currentRunData) {
            $currentMessagePart = sprintf(
                'Profiling run %s: Duration: %0.9f | Memory: %s (%s max)',
                $currentRunData['name'],
                $currentRunData['duration'],
                $this->formatMemory($currentRunData['memory']),
                $this->formatMemory($currentRunData['memoryPeak'])
            );
            if (isset($currentRunData['caller'])) {
                $caller = $currentRunData['caller'];
                $currentMessagePart .= sprintf(
                    ' @ %s:%s',
                    $caller['file'],
                    $caller['line']
                );;
            }
            $messageParts[] = $currentMessagePart;

        }
        $message = implode(PHP_EOL, $messageParts);

        switch ($this->outputHandler) {
            case STDOUT:
            case STDERR:
                fwrite($this->outputHandler, $message);
                break;

            case is_object($this->outputHandler) && method_exists($this->outputHandler, 'log'):
                $this->outputHandler->log(LogLevel::DEBUG, $message);
                break;
        }
        return $message;
    }

    /**
     * Clears the profiling data
     */
    public function clear() {
        $this->profilingData = array();
    }

    /**
     * Outputs a profiling message using the output handler
     *
     * @return array Returns last run data
     */
    public function collectAndOutput() {
        $currentRunData = $this->collect();
        $this->output();
        return $currentRunData;
    }

    /**
     * Outputs a profiling message using the output handler
     *
     * @return array Returns last run data
     */
    public function collectClearAndOutput() {
        $currentRunData = $this->collect();
        $this->output();
        $this->clear();
        return $currentRunData;
    }

    /**
     * Returns the data of the last profiling run
     *
     * @return array
     */
    public function getProfilingData() {
        return $this->profilingData;
    }

    /**
     * Sets the output handler to use
     *
     * The output handler can either be a file handle resource, or an object that responds to log($level, $message)
     *
     * @param resource|object $outputHandler
     */
    public function setOutputHandler($outputHandler) {
        $this->outputHandler = $outputHandler;
    }

    /**
     * Returns the output handler to use
     *
     * The output handler can either be a file handle resource, or an object that responds to log($level, $message)
     *
     * @return resource|object
     */
    public function getOutputHandler() {
        return $this->outputHandler;
    }

    /**
     * Formats the given memory size
     *
     * @param integer $size
     * @return string
     */
    protected function formatMemory($size) {
        $unit = array('B', 'KB', 'MB', 'GB', 'TB', 'PB');
        return @round($size / pow(1024, ($i = intval(floor(log($size, 1024))))), 2) . ' ' . $unit[$i];
    }

    /**
     * Returns the information about the caller of the debug output
     *
     * @return array
     */
    protected function getCaller() {
        static $php54 = NULL;
        if ($php54 === NULL) {
            $php54 = (version_compare(PHP_VERSION, '5.4.0') >= 0);
        }

        if ($php54) {
            $backtrace = debug_backtrace(FALSE, 5);
        } else {
            $backtrace = debug_backtrace(FALSE);
        }
        $backtraceEntry = current($backtrace);
        while (isset($backtraceEntry['class']) && $backtraceEntry['class'] === __CLASS__) {
            $backtraceEntry = next($backtrace);
        }
        return prev($backtrace);
    }

    /**
     * Starts and returns a new profiler instance
     *
     * @param resource|object $outputHandler
     * @return Profiler
     */
    static public function create($outputHandler = STDIN) {
        /** @var Profiler $instance */
        $instance = new static($outputHandler);
        $instance->start();
        return $instance;
    }

    /**
     * Returns a shared profiler instance
     *
     * @param resource|object $outputHandler
     * @return Profiler
     */
    static public function sharedInstance($outputHandler = STDIN) {
        static $instance = NULL;
        if (!$instance) {
            /** @var Profiler $instance */
            $instance = new static($outputHandler);
            $instance->start();
        }
        return $instance;
    }


}