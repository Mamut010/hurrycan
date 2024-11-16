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
     * @param string $callback A callable string that accepts the value to validate as the single argument
     */
    public function __construct(private readonly string $callback, ?bool $each = null, ?string $msg = null) {
        parent::__construct($each, $msg);
    }

    #[\Override]
    protected function execute(ValidationContext $ctx, string $propName, mixed $value): mixed {
        $modelInstance = $ctx->modelInstance();

        if (is_string($this->callback) && method_exists($modelInstance, $this->callback)) {
            $result = Reflections::invokeMethod($modelInstance, $this->callback, $value);
        }
        elseif (is_callable($this->callback)) {
            $result = call_user_func($this->callback, $value);
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
