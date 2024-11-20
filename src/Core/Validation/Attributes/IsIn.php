<?php
namespace App\Core\Validation\Attributes;

use App\Core\Validation\Bases\ArraySupportPropertyValidator;
use App\Core\Validation\ValidationContext;
use App\Utils\Enums;
use Attribute;

/**
 * Validate if a property's value is in a list of values or a case of a {@see BackedEnum}.
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class IsIn extends ArraySupportPropertyValidator
{
    private readonly array $values;

    /**
     * @template TValue
     * @template TBackedEnum of \BackedEnum
     * @param class-string<TBackedEnum>|TValue[] $values
     */
    public function __construct(string|array $enumOrValues, ?bool $each = null, ?string $msg = null) {
        parent::__construct($each, $msg);

        $this->values = $this->getSpecifiedValues($enumOrValues);
    }

    #[\Override]
    protected function execute(ValidationContext $ctx, string $propName, mixed $value): mixed {
        if (empty($this->values)) {
            return null;
        }

        foreach ($this->values as $checkedValue) {
            if ($value === $checkedValue) {
                return null;
            }
        }
        $valuesMessage = $this->getValuesMessage();
        return "'$propName' is not in $valuesMessage";
    }

    #[\Override]
    public function getConstraint(): string {
        $valuesMessage = $this->getValuesMessage();
        return "is in $valuesMessage";
    }

    private function getSpecifiedValues(string|array $enumOrValues) {
        if (is_array($enumOrValues)) {
            return $enumOrValues;
        }

        $values = Enums::getBackedEnumValues($enumOrValues);
        if ($values === false) {
            throw new \InvalidArgumentException("Given string is not a backed enum class");
        }
        return $values;
    }

    private function getValuesMessage() {
        $msgSegments = [];
        foreach ($this->values as $value) {
            if (!isToStringable($value)) {
                return 'the list of specified values';
            }
            $msgSegments[] = strval($value);
        }
        $msg = implode(', ', $msgSegments);
        return "the list [$msg]";
    }
}
