<?php

declare(strict_types=1);

namespace Cundd\Rest\Utility\Profiler;

class Run
{
    /**
     * @var int
     */
    public $runId;

    /**
     * @var string
     */
    public $label;

    /**
     * @var float
     */
    public $profilingStartTime;

    /**
     * @var float
     */
    public $profilingDuration;

    /**
     * @var float
     */
    public $requestStartTime;

    /**
     * @var float
     */
    public $requestDuration;

    /**
     * @var float
     */
    public $runStartTime;

    /**
     * @var float
     */
    public $runEndTime;

    /**
     * @var float
     */
    public $runDuration;

    /**
     * @var float
     */
    public $memory;

    /**
     * @var float
     */
    public $memoryPeak;

    /**
     * @var array
     */
    public $caller;

    /**
     * Run constructor.
     */
    public function __construct(
        int $runId,
        string $name,
        float $profilingStartTime,
        float $profilingDuration,
        float $requestStartTime,
        float $requestDuration,
        float $runStartTime,
        float $runEndTime,
        float $runDuration,
        float $memory,
        float $memoryPeak,
        array $caller,
    ) {
        $this->runId = $runId;
        $this->label = $name;
        $this->profilingStartTime = $profilingStartTime;
        $this->profilingDuration = $profilingDuration;
        $this->requestStartTime = $requestStartTime;
        $this->requestDuration = $requestDuration;
        $this->runStartTime = $runStartTime;
        $this->runEndTime = $runEndTime;
        $this->runDuration = $runDuration;
        $this->memory = $memory;
        $this->memoryPeak = $memoryPeak;
        $this->caller = $caller;
    }
}
