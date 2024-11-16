<?php
namespace App\Core\Validation;

class ValidationResult
{
    private readonly mixed $result;
    private readonly bool $success;

    // No direct instantiation
    private function __construct(mixed $result, bool $success) {
        $this->result = $result;
        $this->success = $success;
    }

    public static function success(): self {
        return new ValidationResult(null, true);
    }

    public static function successValue(mixed $value): self {
        return new ValidationResult($value, true);
    }

    public static function failure(string|ValidationErrorBag $error): self {
        return new ValidationResult($error, false);
    }

    public function isSuccessful(): bool {
        return $this->success;
    }

    public function isFailure(): bool {
        return !$this->isSuccessful();
    }

    public function containsSuccessfulValue(): bool {
        return $this->isSuccessful() && $this->result !== null;
    }

    public function getResult(): mixed {
        return $this->result;
    }

    public function getError(): string|ValidationErrorBag|null {
        return $this->isFailure() ? $this->result : null;
    }
}
