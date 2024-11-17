<?php
namespace App\Core\Validation\Bases;

use App\Core\Validation\Contracts\PropertyValidator;
use App\Core\Validation\Contracts\Validator;
use App\Core\Validation\ValidationContext;
use App\Core\Validation\ValidationErrorBag;
use App\Core\Validation\ValidationResult;

abstract class ArraySupportPropertyValidator implements PropertyValidator
{
    protected readonly bool $each;
    protected readonly ?string $msg;

    /**
     * @param ?bool $each [optional] Specify whether the validation will be applied to each elements in an array property
     * @param ?string $msg [optional] The custom error message
     */
    public function __construct(?bool $each = null, ?string $msg = null) {
        $this->each = $each === true;
        $this->msg = $msg;
    }

    /**
     * Do validation logic per appropriate value in the subject. In case of validating an array,
     * the value is the element of the array in the subject. In other cases, the value is taken
     * directly from the corresponding property of the subject.
     *
     * @template T of object
     * @param ValidationContext<T> $ctx The validation context
     * @param string $propName Name of the property associated with the value
     * @param mixed $value The value to validate, either an element in an array property or the value of a property.
     * @return null|string|object|ValidationErrorBag|ValidationResult The result of the validation operation.
     * A ValidationResult instance can be returned directly. Otherwise, If a string or ValidationErrorBag is
     * returned, it implies a failure. In other cases, it implies a success.
     */
    abstract protected function execute(ValidationContext $ctx, string $propName, mixed $value): mixed;

    /**
     * Get a string describing the constraint associated with this PropertyValidator.
     *
     * @return string A string describing the constraint associated with this PropertyValidator
     */
    abstract protected function getConstraint(): string;

    #[\Override]
    public function validate(ValidationContext $ctx, string $propName): ValidationResult {
        if (!$this->each) {
            $subject = $ctx->subject();
            $result = $this->execute($ctx, $propName, $subject[$propName]);
            return $this->convertExecutionResultToValidationResult($result);
        }
        else {
            return $this->validateArray($ctx, $propName);
        }
    }

    private function validateArray(ValidationContext $ctx, string $propName): ValidationResult {
        $subject = $ctx->subject();
        $values = $subject[$propName];
        if (!is_array($values)) {
            return ValidationResult::failure("'$propName' is not an array");
        }

        $results = [];
        $success = true;
        foreach ($values as $value) {
            $result = $this->execute($ctx, $propName, $value);
            $validationResult = $this->convertExecutionResultToValidationResult($result);
            if ($validationResult->isFailure()) {
                $success = false;
                break;
            }
            if ($validationResult->containsValue()) {
                $results[] = $validationResult->getValue();
            }
        }

        if ($success) {
            return !empty($results) ? ValidationResult::successValue($results) : ValidationResult::success();
        }
        else {
            $constraint = $this->getConstraint();
            $message = $this->getMessage($constraint);
            $message = "an element in '$propName' failed the validation: $message";
            return ValidationResult::failure($message);
        }
    }

    private function convertExecutionResultToValidationResult(mixed $result): ValidationResult {
        if ($result instanceof ValidationResult) {
            return $result;
        }
        elseif (is_string($result) || $result instanceof ValidationErrorBag) {
            $result = is_string($result) ? $this->getMessage($result) : $result;
            return ValidationResult::failure($result);
        }
        else {
            return $result !== null ? ValidationResult::successValue($result) : ValidationResult::success();
        }
    }

    protected function getMessage(string $defaultMsg): string {
        return $this->msg ?? $defaultMsg;
    }
}
