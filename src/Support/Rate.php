<?php
namespace App\Support;

use App\Support\Unit\TimeUnit;
use App\Utils\Converters;

class Rate implements \Stringable, \JsonSerializable
{
    public const SEPARATOR = '/';

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

    #[\Override]
    public function __toString(): string {
        return $this->value . static::SEPARATOR . $this->unit->symbol();
    }

    public function __serialize(): array {
        return [
            'value' => $this->value,
            'unit' => $this->unit->value,
        ];
    }

    public function __unserialize(array $data): void {
        $this->value = $data['value'];
        $this->unit = TimeUnit::from($data['unit']);
    }

    /**
     * Parse a string rate into a {@see Rate} object.
     *
     * @param string $str The string rate
     * @return Rate The parsed {@see Rate} object
     *
     * @throws \InvalidArgumentException If the string is not a valid string rate
     */
    public static function parse(string $str): Rate {
        $segments = explode(static::SEPARATOR, $str);
        if (empty($segments) || count($segments) > 2) {
            throw new \InvalidArgumentException("Invalid string '$str': not a valid rate");
        }

        $valueSegment = $segments[0];
        $value = Converters::strToNumber($valueSegment);
        if ($value === false) {
            $valueSegment = trim($valueSegment);
            throw new \InvalidArgumentException("Invalid value '$valueSegment': not a numeric value");
        }

        if (count($segments) === 2) {
            $unitSegment = trim($segments[1]);
            $unit = TimeUnit::tryFrom(strtolower($unitSegment));
            if (!$unit) {
                throw new \InvalidArgumentException("Invalid unit '$unitSegment': not a valid time unit");
            }
            return new Rate($value, $unit);
        }
        else {
            return new Rate($value);
        }
    }

    /**
     * Create a new rate with the specified unit, adjusting the rate value accordingly.
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
        if ($time <= 0) {
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
        if ($throughput <= 0) {
            return 0;
        }

        $period = $throughput / $this->value;
        if ($unit && $unit !== $this->unit) {
            $ratio = $this->unit->ratio($unit);
            $period *= $ratio;
        }
        return $period;
    }

    #[\Override]
    public function jsonSerialize(): mixed {
        return $this->__toString();
    }
}
