<?php
namespace App\Support\Optional;

/**
 * @template T
 */
class Optional
{
    private static ?Optional $empty = null;

    /**
     * No direct construction allowed. Use of() or empty() instead.
     *
     * @param T $value
     * @param bool $set
     */
    private function __construct(private readonly mixed $value, private readonly bool $set) {
        
    }

    /**
     * Create an Optional containing a given value
     *
     * @template T
     * @param T $value The value of the Optional
     * @return Optional<T> An Optional containing a given value
     */
    public static function of(mixed $value): self {
        return new Optional($value, true);
    }

    /**
     * Create an Optional without any value
     *
     * @template T
     * @return Optional<T> An Optional without any value
     */
    public static function empty(): self {
        if (!static::$empty) {
            static::$empty = new Optional(null, false);
        }
        return static::$empty;
    }

    /**
     * Check if a value is present.
     *
     * @return bool true if a value is present, otherwise return false.
     */
    public function isPresent(): bool {
        return $this->set;
    }

    /**
     * If a value is present, invoke the specified callback with the value, otherwise do nothing.
     *
     * @param callable(T $value):void $callback The callback to invoke if a value is present
     * @return void
     */
    public function ifPresent(callable $callback): void {
        if ($this->set) {
            call_user_func($callback, $this->value);
        }
    }

    /**
     * @return T
     * @throws ValueNotExistException
     */
    public function get(): mixed {
        if (!$this->set) {
            throw new ValueNotExistException('No value has been set for the Optional');
        }
        return $this->value;
    }

    /**
     * @param T $default
     * @return T
     */
    public function getOrElse(mixed $default): mixed {
        return $this->set ? $this->value : $default;
    }
}
