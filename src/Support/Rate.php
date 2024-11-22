<?php
namespace App\Support;

use App\Support\Unit\TimeUnit;

class Rate
{
    /**
     * @param int|float $value The rate value
     * @param TimeUnit $unit [optional] The time unit of the rate. Defauled to {@see TimeUnit::SECOND}.
     */
    public function __construct(
        private readonly int|float $value,
        private readonly TimeUnit $unit = TimeUnit::SECOND
    ) {
        if ($value < 0) {
            $value = 0;
        }
    }

    /**
     * Create a new rate with the specified unit, adjusting the rate value accordingly. Precision is kept the same.
     *
     * @param TimeUnit $unit The time unit to change into
     * @return Rate A new rate with the same configuration but different time unit
     */
    public function newUnit(TimeUnit $unit): Rate {
        $newValue = $this->value;
        if ($unit !== $this->unit) {
            $ratio = $this->unit->ratio($unit);
            $newValue *= $ratio;
        }
        return new Rate($newValue, $unit);
    }

    /**
     * Calculate throughput value after a specified time.
     *
     * @param int|float $time The elapsed time
     * @param TimeUnit $unit [optional] The time unit of the elapsed time. Defaulted to {@see TimeUnit::SECOND}
     * @return int|float The throughput value
     */
    public function calculateThroughput(int|float $time, TimeUnit $unit = TimeUnit::SECOND): int|float {
        if ($time < 0) {
            return 0;
        }

        $throughput = $time * $this->value;
        if ($unit !== $this->unit) {
            $ratio = $unit->ratio($this->unit);
            $throughput *= $ratio;
        }
        return $throughput;
    }

    /**
     * Calculate the amount of time needed to reach a specified throughput.
     *
     * @param int|float $throughput The throughput to reach
     * @param TimeUnit $unit [optional] The time unit of the output period
     *                       If not specified, the time unit of this {@see Rate} object is used
     * @return int|float The amount of time needed to reach the specified throughput
     */
    public function calculateTime(int|float $throughput, ?TimeUnit $unit = null): int|float {
        $period = $throughput / $this->value;
        if ($unit && $unit !== $this->unit) {
            $ratio = $this->unit->ratio($unit);
            $period *= $ratio;
        }
        return $period;
    }
}
