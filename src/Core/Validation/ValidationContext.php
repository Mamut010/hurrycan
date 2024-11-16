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
     * @param string $propName The name of the property to validate in the validation model
     */
    public function __construct(
        private readonly Validator $validator,
        private readonly object $modelInstance,
        private readonly array $subject,
        private readonly string $propName,
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
     * @return string The name of the property to validate in the validation model
     */
    public function propertyName(): string {
        return $this->propName;
    }

    public function subjectPropertyValue(): mixed {
        return $this->subject[$this->propName];
    }
}
