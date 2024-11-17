<?php
namespace App\Core\Validation;

use App\Core\Exceptions\ValidationResultNotExistException;
use App\Support\Optional\Optional;
use App\Support\Optional\ValueNotExistException;

class ValidationResult
{
    private static ?ValidationResult $emptySuccess = null;

    /**
     * No direct construction allowed. Use any of success(), sucessValue() or failure() instead.
     *
     * @param Optional<mixed> $value
     * @param bool $success
     */
    private function __construct(private readonly Optional $value, private readonly bool $success) {

    }

    public static function success(): self {
        if (!static::$emptySuccess) {
            static::$emptySuccess = new ValidationResult(Optional::empty(), true);
        }
        return static::$emptySuccess;
    }

    public static function successValue(mixed $value): self {
        return new ValidationResult(Optional::of($value), true);
    }

    public static function failure(string|ValidationErrorBag $error): self {
        return new ValidationResult(Optional::of($error), false);
    }

    public function isSuccessful(): bool {
        return $this->success;
    }

    public function isFailure(): bool {
        return !$this->isSuccessful();
    }

    public function containsValue(): bool {
        return $this->value->isPresent();
    }

    public function containsSuccessfulValue(): bool {
        return $this->isSuccessful() && $this->containsValue();
    }

    /**
     * @return mixed
     * @throws ValidationResultNotExistException
     */
    public function getValue(): mixed {
        if (!$this->value->isPresent()) {
            throw new ValidationResultNotExistException();
        }

        return $this->value->get();
    }

    public function getError(): string|ValidationErrorBag|null {
        return $this->isFailure() ? $this->value->getOrElse(null) : null;
    }
}
