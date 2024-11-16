<?php
namespace App\Core\Validation\Bases;

use App\Core\Validation\Contracts\PropertyValidator;
use App\Core\Validation\Contracts\Validator;
use App\Core\Validation\ValidationErrorBag;
use App\Core\Validation\ValidationResult;

abstract class ArraySupportPropertyValidator implements PropertyValidator
{
    protected readonly bool $each;

    public function __construct(?bool $each = null) {
        $this->each = $each === true;
    }

    /**
     * Do validation logic per appropriate value in the subject. In case of validating an array,
     * the value is the element of the array in the subject. In other cases, the value is taken
     * directly from the corresponding property in subject.  
     * 
     * @param Validator $validator The validator used in the current validation context
     * @param array<string,mixed> $subject The subject to validate
     * @param string $propName The name of the property to validate in the validation model
     * @param mixed $value The value to validate
     * @return null|string|object|ValidationErrorBag|ValidationResult The result of the validation operation.
     * A ValidationResult instance can be returned directly. Otherwise, If a string or ValidationErrorBag is
     * returned, it implies a failure. In other cases, it implies a success.
     */
    abstract protected function execute(Validator $validator, array $subject, string $propName, mixed $value): mixed;

    /**
     * Get a string describing the constraint associated with this PropertyValidator.
     *
     * @return string A string describing the constraint associated with this PropertyValidator
     */
    abstract protected function getConstraint(): string;

    #[\Override]
    public function validate(Validator $validator, array $subject, string $propName): ValidationResult {
        if (!$this->each) {
            $result = $this->execute($validator, $subject, $propName, $subject[$propName]);
            return $this->convertExecutionResultToValidationResult($result);
        }
        else {
            return $this->validateArray($validator, $subject, $propName);
        }
    }

    private function validateArray(Validator $validator, array $subject, string $propName): ValidationResult {
        $values = $subject[$propName];
        if (!is_array($values)) {
            return ValidationResult::failure("'$propName' is not an array");
        }

        $results = [];
        $success = true;
        foreach ($values as $value) {
            $result = $this->execute($validator, $subject, $propName, $value);
            $validationResult = $this->convertExecutionResultToValidationResult($result);
            if ($validationResult->isFailure()) {
                $success = false;
                break;
            }

            $result = $validationResult->getResult();
            if ($result !== null) {
                $results[] = $result;
            }
        }

        if ($success) {
            return !empty($results) ? ValidationResult::successValue($results) : ValidationResult::success();
        }
        else {
            $constraint = $this->getConstraint();
            $msg = "an element in '$propName' failed the constraint: $constraint";
            return ValidationResult::failure($msg);
        }
    }

    private function convertExecutionResultToValidationResult(mixed $result): ValidationResult {
        if ($result instanceof ValidationResult) {
            return $result;
        }
        elseif (is_string($result) || $result instanceof ValidationErrorBag) {
            return ValidationResult::failure($result);
        }
        else {
            return $result !== null ? ValidationResult::successValue($result) : ValidationResult::success();
        }
    }
}
