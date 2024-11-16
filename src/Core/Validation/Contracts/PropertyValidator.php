<?php
namespace App\Core\Validation\Contracts;

use App\Core\Validation\Contracts\Validator;
use App\Core\Validation\ValidationResult;

interface PropertyValidator
{
    /**
     * Validate a given property of a subject. Return an error message
     * if the subject failed the validation. Otherwise, return null.
     *
     * @param Validator $validator The validator used in the current validation context
     * @param array<string,mixed> $subject The subject to validate
     * @param string $propName The name of the property to validate in the validation model
     * @return ValidationResult The validation result, either successful or failed.
     */
    function validate(Validator $validator, array $subject, string $propName): ValidationResult;
}
