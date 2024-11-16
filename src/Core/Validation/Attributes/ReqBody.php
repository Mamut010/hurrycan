<?php
namespace App\Core\Validation\Attributes;

use App\Core\Http\Request\Request;
use App\Core\Validation\Bases\RequestValidation;
use Attribute;

#[Attribute(Attribute::TARGET_PARAMETER)]
class ReqBody extends RequestValidation
{
    #[\Override]
    protected function getSubject(Request $request): array {
        return $request->body();
    }
}
