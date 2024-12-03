<?php
namespace App\Support\Unit;

enum TimeUnit: string
{
    case NANO_SECOND = 'ns';
    case MICRO_SECOND = 'us';
    case MILLI_SECOND = 'ms';
    case SECOND = 's';
    case MINUTE = 'm';
    case HOUR = 'h';
    case DAY = 'd';

    /**
     * Get the scaling factor in nanoseconds.
     */
    public function nanoScale(): int|float {
        return match ($this) {
            TimeUnit::NANO_SECOND => 1,
            TimeUnit::MICRO_SECOND => 1_000,
            TimeUnit::MILLI_SECOND => 1_000_000,
            TimeUnit::SECOND => 1_000_000_000,
            TimeUnit::MINUTE => 6_000_000_0000,
            TimeUnit::HOUR => 3_600_000_000_000,
            TimeUnit::DAY => 86_400_000_000_000,
        };
    }

    /**
     * Get the conversion ratio of current time unit to another time unit
     */
    public function ratio(TimeUnit $other): int|float {
        return $this->nanoScale() / $other->nanoScale();
    }

    /**
     * Get the symbol for the time unit.
     */
    public function symbol(): string {
        return $this->value;
    }
}
