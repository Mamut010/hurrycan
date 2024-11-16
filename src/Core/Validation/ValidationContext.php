<?php
namespace App\Core\Validation;

use App\Core\Validation\Contracts\Validator;

/**
 * @template T of object
 */
class ValidationContext
{
    /**
     * @param Validator $validator The validator used in the current validation context
     * @param T $modelInstance An instance of validation model
     * @param array<string,mixed> $subject The subject to validate
     * @param string[] $passedProps The name of the properties that passed the validation
     * @param string[] $errorProps The name of the properties that failed the validation
     */
    public function __construct(
        private readonly Validator $validator,
        private readonly object $modelInstance,
        private readonly array $subject,
        private array &$passedProps,
        private array &$errorProps,
    ) {
        
    }

    /**
     * @return Validator The validator used in the current validation context
     */
    public function validator(): Validator {
        return $this->validator;
    }

    /**
     * @return T An instance of validation model in the current validation context
     */
    public function modelInstance(): object {
        return $this->modelInstance;
    }

    /**
     * @return array<string,mixed> The subject to validate in the current validation context
     */
    public function subject(): array {
        return $this->subject;
    }

    /**
     * @return string[] an array containing names of the properties that passed the validation
     */
    public function passedProperties(): array {
        return $this->passedProps;
    }

    /**
     * @return string[] an array containing names of the properties that failed the validation
     */
    public function errorProperties(): array {
        return $this->errorProps;
    }
}
