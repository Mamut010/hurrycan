<?php
namespace App\Core\Validation\Contracts;

use App\Core\Validation\ValidationContext;
use App\Core\Validation\ValidationResult;

interface PropertyValidator
{
    /**
     * Validate a given property of a subject. Return a validation result representing either a success or failure.
     *
     * @param ValidationContext $ctx The validation context
     * @return ValidationResult The validation result, either successful or failed.
     */
    function validate(ValidationContext $ctx): ValidationResult;
}
