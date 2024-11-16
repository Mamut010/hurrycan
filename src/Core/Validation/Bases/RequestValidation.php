<?php
namespace App\Core\Validation\Bases;

use App\Core\Http\Request\Request;
use App\Core\Validation\Contracts\Validator;
use App\Core\Validation\ValidationErrorBag;

abstract class RequestValidation
{
    public function __construct(public readonly ?string $errorMessage = null) {
        
    }

    abstract protected function getSubject(Request $request): array;

    /**
     * @template T of object
     * @param Validator $validator
     * @param Request $request
     * @param ?string $validationModel
     * @return T|ValidationErrorBag|array
     */
    public function invoke(Validator $validator, Request $request, ?string $validationModel): object|array {
        $subject = $this->getSubject($request);
        if ($validationModel !== null) {
            return $validator->validate($subject, $validationModel);
        }
        else {
            return $subject;
        }
    }
}