<?php
namespace App\Core\Validation\Attributes;

use App\Core\Http\Request\Request;
use App\Core\Validation\Bases\RequestValidation;
use Attribute;

/**
 * Validate the instance of {@see Request}'s route parameters and inject the result if success
 * or throw a {@see HttpException} with status 400 - Bad Request - on failure.
 */
#[Attribute(Attribute::TARGET_PARAMETER)]
class ReqRoute extends RequestValidation
{
    #[\Override]
    protected function getSubject(Request $request): array {
        return $request->routeParams();
    }
}
