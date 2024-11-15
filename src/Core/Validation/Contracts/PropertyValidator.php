<?php
namespace App\Core\Validation\Contracts;

interface PropertyValidator
{
    /**
     * Validate a given property of a subject. Return an error message
     * if the subject failed the validation. Otherwise, return null.
     *
     * @param \ReflectionProperty $prop The property to validate in the validation model
     * @param array<string,mixed> $subject The subject to validate
     * @param mixed $value the validated value of the subject
     * @return ?string null if the subject passes the validation. Otherwise, an error message is returned
     */
    function validate(\ReflectionProperty $prop, array $subject, mixed $value): ?string;
}
