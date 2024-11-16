<?php
namespace App\Core\Validation\Attributes;

use App\Core\Validation\Bases\ArraySupportPropertyValidator;
use App\Core\Validation\ValidationContext;
use App\Utils\Reflections;
use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class SatisfyCallback extends ArraySupportPropertyValidator
{
    /**
     * @param string $callback A callable string that accepts the value to validate as the first argument,
     * the second argument is the passed properties so far, the third argument
     * is the names of properties that failed the validation so far.
     * @param ?bool $each [optional] Specify whether the validation will be applied to each elements in an array property
     * @param ?string $msg [optional] The custom error message
     */
    public function __construct(private readonly string $callback, ?bool $each = null, ?string $msg = null) {
        parent::__construct($each, $msg);
    }

    #[\Override]
    protected function execute(ValidationContext $ctx, string $propName, mixed $value): mixed {
        $modelInstance = $ctx->modelInstance();
        $passProps = $ctx->passedProperties();
        $errorProps = $ctx->errorProperties();

        if (is_string($this->callback) && method_exists($modelInstance, $this->callback)) {
            $result = Reflections::invokeMethod($modelInstance, $this->callback, $value, $passProps, $errorProps);
        }
        elseif (is_callable($this->callback)) {
            $result = call_user_func($this->callback, $value, $passProps, $errorProps);
        }
        else {
            throw new \InvalidArgumentException("Invalid callback [$this->callback] provided");
        }

        $success = boolval($result);
        if (!$success) {
            return "'$propName' does not satisfy the callback '$this->callback'";
        }
        else {
            return null;
        }
    }

    #[\Override]
    protected function getConstraint(): string {
        return "satisfy callback '$this->callback'";
    }
}
