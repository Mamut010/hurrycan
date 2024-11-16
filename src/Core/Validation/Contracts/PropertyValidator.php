<?php
namespace App\Core\Validation\Contracts;

use App\Core\Validation\ValidationContext;
use App\Core\Validation\ValidationResult;

interface PropertyValidator
{
    /**
     * Validate a given property of a subject. Return a validation result representing either a success or failure.
     *
     * @template T of object
     * @param ValidationContext<T> $ctx The validation context
     * @param string $propName The name of the property to validate in the validation model
     * @return ValidationResult The validation result, either successful or failed.
     */
    function validate(ValidationContext $ctx, string $propName): ValidationResult;
}
