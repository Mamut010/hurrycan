<?php
namespace App\Core\Validation;

class ValidationResult
{
    // No direct instantiation
    private function __construct(
        private readonly mixed $result,
        private readonly bool $success,
        private readonly bool $containsValue
    ) {

    }

    public static function success(): self {
        return new ValidationResult(null, true, false);
    }

    public static function successValue(mixed $value): self {
        return new ValidationResult($value, true, true);
    }

    public static function failure(string|ValidationErrorBag $error): self {
        return new ValidationResult($error, false, true);
    }

    public function isSuccessful(): bool {
        return $this->success;
    }

    public function isFailure(): bool {
        return !$this->isSuccessful();
    }

    public function containsValue(): bool {
        return $this->containsValue;
    }

    public function containsSuccessfulValue(): bool {
        return $this->isSuccessful() && $this->containsValue();
    }

    public function getResult(): mixed {
        return $this->result;
    }

    public function getError(): string|ValidationErrorBag|null {
        return $this->isFailure() ? $this->result : null;
    }
}
