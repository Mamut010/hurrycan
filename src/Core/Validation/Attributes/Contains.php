<?php
namespace App\Core\Validation\Attributes;

use App\Core\Validation\ValidationContext;
use App\Utils\Arrays;
use App\Utils\Strings;
use Attribute;

/**
 * Validate if a string property's value contains a substring. If multiple substrings are provided,
 * the validator will perform an OR operation.
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class Contains extends IsString
{
    /**
     * @var string[]
     */
    private readonly array $substrs;
    private readonly bool $caseInsensitive;

    /**
     * @param string|string[] $substr The substring or array of substrings to check for
     * @param ?bool $caseInsensitive [optional] Whether to validate in case-insensitive manner
     * @throws \InvalidArgumentException If no substring or a non-string substring is provided
     */
    public function __construct(
        string|array $substr,
        ?bool $caseInsensitive = null,
        ?bool $each = null,
        ?string $msg = null
    ) {
        $substrs = Arrays::asArray($substr);
        $this->assertValidSubstrings($substrs);

        parent::__construct($each, $msg);

        $this->substrs = $substrs;
        $this->caseInsensitive = $caseInsensitive === true;
    }

    #[\Override]
    protected function execute(ValidationContext $ctx, string $propName, mixed $value): mixed {
        $msg = parent::execute($ctx, $propName, $value);
        if ($msg !== null) {
            return $msg;
        }

        if (!$this->checkValue($value)) {
            $substrMessage = $this->getSubstringMessage();
            $msg = "'$propName' does not contain $substrMessage";
        }

        return $msg;
    }

    #[\Override]
    public function getConstraint(): string {
        $substrMessage = $this->getSubstringMessage();
        return "contain $substrMessage";
    }

    /**
     * @param string[] $substrs
     */
    private function assertValidSubstrings(array $substrs) {
        if (empty($substrs)) {
            throw new \InvalidArgumentException('At least one substring must be specified');
        }

        $nonStrings = Arrays::findFirst($substrs, fn($item) => !is_string($item));
        if (!empty($nonStrings)) {
            $msg = count($substrs) === 1
                ? "Given substring is not a valid substring"
                : "Given substrings contain an invalid substring";
            throw new \InvalidArgumentException($msg);
        }
    }

    private function checkValue(string $value) {
        if ($this->caseInsensitive) {
            $pred = fn(string $value, string $substr) => Strings::icontains($value, $substr);
        }
        else {
            $pred = fn(string $value, string $substr) => str_contains($value, $substr);
        }

        foreach ($this->substrs as $substr) {
            if (call_user_func($pred, $value, $substr)) {
                return true;
            }
        }
        return false;
    }

    private function getSubstringMessage() {
        $caseMsg = $this->caseInsensitive ? ' (case-insensitive)' : '';
        if (count($this->substrs) === 1) {
            $substr = $this->substrs[0];
            return "substring $substr" . $caseMsg;
        }
        else {
            $substrs = array_map(fn(string $str) => "'$str'", $this->substrs);
            $lastSubstr = array_pop($substrs);
            $substrOrMessage = implode(', ', $substrs) . ' or ' . $lastSubstr;
            return "substrings $substrOrMessage" . $caseMsg;
        }
    }
}
