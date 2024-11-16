<?php
namespace App\Core\Validation\Attributes;

use App\Constants\Delimiter;
use App\Core\Validation\Bases\ArraySupportPropertyValidator;
use App\Core\Validation\Contracts\Validator;
use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class ValidateNested extends ArraySupportPropertyValidator
{
    public function __construct(private readonly string $validationModel, ?bool $each = null) {
        try {
            new \ReflectionClass($validationModel);
        }
        catch (\ReflectionException $e) {
            throw new \InvalidArgumentException("Invalid validation model");
        }

        parent::__construct($each);
    }

    #[\Override]
    protected function execute(Validator $validator, array $subject, string $propName, mixed $value): mixed {
        if (!is_array($value) && !is_object($value)) {
            return "'$propName' does not satisfy the validation model $this->validationModel";
        }
        return $validator->validate($value, $this->validationModel);
    }

    #[\Override]
    protected function getConstraint(): string {
        $modelFullnameSegments = explode(Delimiter::NAMESPACE, $this->validationModel);
        $modelName = $modelFullnameSegments[count($modelFullnameSegments) - 1];
        return "satisfies validation model [$modelName]";
    }
}
