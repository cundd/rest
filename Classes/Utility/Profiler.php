<?php
declare(strict_types=1);

namespace Cundd\Rest\Utility;

use Cundd\Rest\Utility\Profiler\Run;
use Psr\Log\LogLevel;

/**
 * A simple profiling utility
 */
class Profiler
{
    public const OUTPUT_ECHO = 'echo';
    public const OUTPUT_STDOUT = STDOUT;
    public const OUTPUT_STDERR = STDERR;

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
    protected $options = [];

    /**
     * Data of the last run
     *
     * @var Run[]
     */
    protected $profilingData = [];

    /**
     * @var resource|object
     */
    protected $defaultOutputHandler;

    /**
     * @var string
     */
    private $defaultLabel = '';

    /**
     * @var boolean
     */
    private $collectCaller = true;

    /**
     * @param resource|object|bool|string $defaultOutputHandler Output handler to use
     */
    public function __construct($defaultOutputHandler = STDOUT)
    {
        $this->defaultOutputHandler = $defaultOutputHandler;
    }

    /**
     * Start the profiling
     *
     * @param array|string $options Additional options to pass to the profiler. If a string is given it will be used as name
     * @return $this
     */
    public function start($options = []): self
    {
        if (is_string($options)) {
            $this->defaultLabel = $options;
        } elseif (isset($options['label'])) {
            $this->defaultLabel = $options['label'];
        } elseif (isset($options['name'])) {
            $this->defaultLabel = $options['name'];
        }

        if (isset($options['collectCaller'])) {
            $this->collectCaller = $options['collectCaller'];
        }
        $this->options = $options;
        $this->profilingData = [];
        $this->startTime = microtime(true);

        return $this;
    }

    /**
     * Collect and returns profiling data of the current run
     *
     * @param string|null $label Optional label for the run
     * @return Run
     */
    public function collect(string $label = null): Run
    {
        $runEndTime = microtime(true);
        $runId = count($this->profilingData);
        if ($runId > 0) {
            $lastRunData = end($this->profilingData);
            $runStartTime = $lastRunData->runEndTime;
        } else {
            $runStartTime = $this->startTime;
        }

        $requestStartTime = $_SERVER['REQUEST_TIME_FLOAT'];
        $name = $label ?? $this->defaultLabel;
        $caller = $this->collectCaller ? $this->getCaller() : [];

        $run = new Run(
            $runId,                             // Run ID
            $name,                              // Name
            $this->startTime,                   // Profiling Start Time
            $runEndTime - $this->startTime,     // Profiling Duration
            $requestStartTime,                  // Request Start Time
            $runEndTime - $requestStartTime,    // Request Duration
            $runStartTime,                      // Run Start Time
            $runEndTime,                        // Run End Time
            $runEndTime - $runStartTime,        // Run Duration
            memory_get_usage(true),             // Memory
            memory_get_peak_usage(true),        // Peak Memory
            $caller                             // Caller
        );
        $this->profilingData[$runId] = $run;

        return $run;
    }

    /**
     * Output a profiling message using the output handler
     *
     * @param resource|object|string|null $outputHandler Optionally specify the output handler
     * @return string Returns the message
     */
    public function output($outputHandler = null): string
    {
        return $this->outputProfilingData(
            $this->profilingData,
            $outputHandler
        );
    }

    /**
     * Output a profiling message using the output handler
     *
     * @param resource|object|string|null $outputHandler Optionally specify the output handler
     * @return string Returns the message
     */
    public function outputLast($outputHandler = null): string
    {
        $profilingData = $this->profilingData;

        return $this->outputProfilingData(
            [count($profilingData) => end($profilingData)],
            $outputHandler
        );
    }

    /**
     * Clear the profiling data
     */
    public function clear()
    {
        $this->profilingData = [];
    }

    /**
     * Output a profiling message using the output handler
     *
     * @param resource|object|string|null $outputHandler Optionally specify the output handler
     * @param string|null                 $label         Optional label for the run
     * @return Run Returns data of the last run
     */
    public function collectAndOutput($outputHandler = null, string $label = null): Run
    {
        $currentRunData = $this->collect($label);
        $this->output($outputHandler);

        return $currentRunData;
    }

    /**
     * Output a profiling message using the output handler
     *
     * @param resource|object|string|null $outputHandler Optionally specify the output handler
     * @param string|null                 $label         Optional label for the run
     * @return Run Returns data of the last run
     */
    public function collectAndOutputLast($outputHandler = null, string $label = null): Run
    {
        $currentRunData = $this->collect($label);
        $this->outputLast($outputHandler);

        return $currentRunData;
    }

    /**
     * Output a profiling message using the output handler
     *
     * @param resource|object|string|null $outputHandler Optionally specify the output handler
     * @param string|null                 $label         Optional label for the run
     * @return Run Returns data of the last run
     */
    public function collectClearAndOutput($outputHandler = null, string $label = null): Run
    {
        $currentRunData = $this->collect($label);
        $this->output($outputHandler);
        $this->clear();

        return $currentRunData;
    }

    /**
     * Return the data of the last profiling run
     *
     * @return Run[]
     */
    public function getProfilingData(): array
    {
        return $this->profilingData;
    }

    /**
     * Set the output handler to use
     *
     * The output handler can either be a file handle resource, or an object that responds to log($level, $message)
     *
     * @param resource|object $defaultOutputHandler
     */
    public function setDefaultOutputHandler($defaultOutputHandler)
    {
        $this->defaultOutputHandler = $defaultOutputHandler;
    }

    /**
     * Return the output handler to use
     *
     * The output handler can either be a file handle resource, or an object that responds to log($level, $message)
     *
     * @return resource|object
     */
    public function getDefaultOutputHandler()
    {
        return $this->defaultOutputHandler;
    }

    /**
     * Formats the given memory size
     *
     * @param float|int $size
     * @return string
     */
    protected function formatMemory($size): string
    {
        $unit = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];

        return @round($size / pow(1024, ($i = intval(floor(log($size, 1024))))), 2) . ' ' . $unit[$i];
    }

    /**
     * Return the information about the caller of the debug output
     *
     * @return array
     */
    protected function getCaller(): array
    {
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5);
        array_shift($backtrace);
        array_shift($backtrace);
        $backtraceEntry = current($backtrace);
        while (isset($backtraceEntry['class']) && $backtraceEntry['class'] === __CLASS__) {
            $backtraceEntry = next($backtrace);
        }

        $callingEntry = prev($backtrace);

        return $callingEntry ?: ($backtraceEntry ?: []);
    }

    /**
     * Starts and returns a new profiler instance
     *
     * @param resource|object|bool $defaultOutputHandler
     * @return Profiler
     */
    public static function create($defaultOutputHandler = STDOUT): self
    {
        $instance = new static($defaultOutputHandler);
        $instance->start();

        return $instance;
    }

    /**
     * Return a shared profiler instance
     *
     * @param resource|object|bool $defaultOutputHandler
     * @return Profiler
     */
    public static function sharedInstance($defaultOutputHandler = STDOUT): self
    {
        static $instance = null;
        if (!$instance) {
            $instance = new static($defaultOutputHandler);
            $instance->start();
        }

        return $instance;
    }

    /**
     * @param Run[]                       $profilingData
     * @param resource|object|string|null $outputHandler
     * @return string
     */
    private function outputProfilingData(array $profilingData, $outputHandler): string
    {
        if (empty($profilingData)) {
            return '';
        }

        $maxLabelLength = $this->getMaxLabelLengthOfProfilingData();

        $messageParts = [];
        foreach ($profilingData as $index => $currentRunData) {
            $runDurationMs = $currentRunData->runDuration * 1000;
            $requestDurationMs = $currentRunData->requestDuration * 1000;

            $label = str_pad($currentRunData->label, $maxLabelLength, ' ');
            $currentMessagePart = sprintf(
                'Profiling run #%05d: Duration: % 11.6fms | Since request: % 11.6fms | Memory: %s (%s max)',
                $currentRunData->runId,
                $runDurationMs,
                $requestDurationMs,
                $this->formatMemory($currentRunData->memory),
                $this->formatMemory($currentRunData->memoryPeak)
            );
            if (!empty($currentRunData->caller)) {
                $caller = $currentRunData->caller;
                $currentMessagePart .= sprintf(
                    ' @ %s:%s',
                    $caller['file'],
                    $caller['line']
                );
            }

            if ($label) {
                $currentMessagePart .= ' ' . $label;
            }

            $messageParts[] = $currentMessagePart;
        }
        $message = implode(PHP_EOL, $messageParts) . PHP_EOL;

        $effectiveOutputHandler = $outputHandler !== null ? $outputHandler : $this->defaultOutputHandler;
        if (is_resource($effectiveOutputHandler)) {
            fwrite($effectiveOutputHandler, $message);
        } elseif (self::OUTPUT_ECHO === $effectiveOutputHandler) {
            echo $message;
        } elseif (is_object($effectiveOutputHandler) && method_exists($effectiveOutputHandler, 'log')) {
            $effectiveOutputHandler->log(LogLevel::DEBUG, $message);
        }

        return $message;
    }

    private function getMaxLabelLengthOfProfilingData()
    {
        return array_reduce(
            $this->profilingData,
            function ($carry, Run $item): int {
                $labelLength = strlen($item->label);

                return $labelLength > $carry ? $labelLength : $carry;
            },
            0
        );
    }
}
