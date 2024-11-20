<?php
namespace App\Core\Validation\Attributes;

use App\Core\Http\Request\Request;
use App\Core\Validation\Bases\RequestValidation;
use Attribute;

/**
 * Validate the instance of {@see Request}'s inputs and inject the result if success
 * or throw a {@see HttpException} with status 400 - Bad Request - on failure.
 */
#[Attribute(Attribute::TARGET_PARAMETER)]
class ReqInputs extends RequestValidation
{
    #[\Override]
    protected function getSubject(Request $request): array {
        return $request->inputs();
    }
}
