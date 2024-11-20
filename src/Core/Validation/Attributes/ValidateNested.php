<?php
namespace App\Core\Validation\Attributes;

use App\Constants\Delimiter;
use App\Core\Validation\Bases\ArraySupportPropertyValidator;
use App\Core\Validation\ValidationContext;
use App\Utils\Arrays;
use Attribute;

/**
 * Validate if a property satisfies a validation model.
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class ValidateNested extends ArraySupportPropertyValidator
{
    public function __construct(private readonly string $validationModel, ?bool $each = null, ?string $msg = null) {
        try {
            new \ReflectionClass($validationModel);
        }
        catch (\ReflectionException $e) {
            throw new \InvalidArgumentException("Invalid validation model");
        }

        parent::__construct($each, $msg);
    }

    #[\Override]
    protected function execute(ValidationContext $ctx, string $propName, mixed $value): mixed {
        if (!Arrays::isAssocArray($value) && !is_object($value)) {
            $modelName = $this->getModelName();
            return "'$propName' does not satisfy the validation model $modelName";
        }
        return $ctx->validator()->validate($value, $this->validationModel);
    }

    #[\Override]
    public function getConstraint(): string {
        $modelName = $this->getModelName();
        return "satisfies validation model [$modelName]";
    }

    private function getModelName() {
        $modelFullnameSegments = explode(Delimiter::NAMESPACE, $this->validationModel);
        return $modelFullnameSegments[count($modelFullnameSegments) - 1];
    }
}
