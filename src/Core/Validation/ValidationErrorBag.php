<?php
namespace App\Core\Validation;

class ValidationErrorBag implements \IteratorAggregate, \JsonSerializable
{
    /**
     * @var array<string,ValidationErrorbag|string>
     */
    private array $errors = [];

    public function isEmpty(): bool {
        return empty($this->errors);
    }

    public function size(): int {
        return count($this->errors);
    }

    public function contains(string $key) {
        return isset($this->errors[$key]);
    }

    public function add(string $key, ValidationErrorbag|string $error, bool $replace = true): bool {
        if ($replace || !$this->contains($key)) {
            $this->errors[$key] = $error;
            return true;
        }
        else {
            return false;
        }
    }

    public function remove(string $key): ValidationErrorbag|string|false {
        if($this->contains($key)) {
            $error = $this->errors[$key];
            unset($this->errors[$key]);
            return $error;
        }
        else {
            return null;
        }
    }

    public function get(string $key): ValidationErrorbag|string|null {
        if($this->contains($key)) {
            return $this->errors[$key];
        }
        else {
            return null;
        }
    }

    /**
     * @return string[]
     */
    public function keys(): array {
        return array_keys($this->errors);
    }

    /**
     * @return (ValidationErrorbag|string)[]
     */
    public function values(): array {
        return array_values($this->errors);
    }

    #[\Override]
    public function getIterator(): \Traversable {
        return new \ArrayIterator($this->errors);
    }

    #[\Override]
    public function jsonSerialize(): mixed {
        return $this->errors;
    }
}
