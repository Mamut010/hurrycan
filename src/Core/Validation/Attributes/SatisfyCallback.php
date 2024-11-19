<?php
namespace App\Core\Validation\Attributes;

use App\Core\Validation\Bases\ArraySupportPropertyValidator;
use App\Core\Validation\ValidationContext;
use App\Utils\Functions;
use App\Utils\Reflections;
use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class SatisfyCallback extends ArraySupportPropertyValidator
{
    /**
     * @param string|array $callback A callable string or array that accepts the value to validate
     * as the first argument, the second argument is the passed properties so far, the third argument
     * is the names of propertiesthat failed the validation so far.
     * @param ?bool $each [optional] Specify whether the validation will be applied to each elements in an array property
     * @param ?string $msg [optional] The custom error message
     */
    public function __construct(private readonly string|array $callback, ?bool $each = null, ?string $msg = null) {
        parent::__construct($each, $msg);
    }

    #[\Override]
    protected function execute(ValidationContext $ctx, string $propName, mixed $value): mixed {
        $modelInstance = $ctx->modelInstance();
        $passProps = $ctx->passedProperties();
        $errorProps = $ctx->errorProperties();

        $result = Reflections::invokeMethodOrCallNoInstance(
            $modelInstance,
            $this->callback,
            $value,
            $passProps,
            $errorProps
        );

        $success = boolval($result);
        if (!$success) {
            $callbackName = Functions::getPossibleCallbackName($this->callback);
            return "'$propName' does not satisfy the callback '$callbackName'";
        }
        else {
            return null;
        }
    }

    #[\Override]
    protected function getConstraint(): string {
        $callbackName = Functions::getPossibleCallbackName($this->callback);
        return "satisfy callback '$callbackName'";
    }
}
