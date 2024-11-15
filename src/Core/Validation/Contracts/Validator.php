<?php
namespace App\Core\Validation\Contracts;

interface Validator
{
    /**
     * Validate a given subject against a validation model and return the instantiated model
     * if success or an array containing all validation errors on failure.
     *
     * @template T of object
     * @param array<string,mixed>|object $subject The subject to validate
     * @param class-string<T> $validationModel The validation model to validate against
     * @return T|ValidationErrorBag An instance of the validation model on success. Othewise,
     * a {@see ValidationErrorBag} instance containing all validation error messages is returned.
     */
    function validate(array|object $subject, string $validationModel): object;
}
