<?php
namespace App\Core\Validation\Attributes;

use App\Constants\Delimiter;
use App\Core\Validation\Contracts\Validator;
use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class IsPattern extends IsString
{
    public function __construct(private readonly string $pattern, ?bool $each = null) {
        parent::__construct($each);
    }

    #[\Override]
    protected function execute(Validator $validator, array $subject, string $propName, mixed $value): mixed {
        $msg = parent::execute($validator, $subject, $propName, $value);
        if ($msg !== null) {
            return $msg;
        }
        
         // Escape special regex characters in the pattern
        $escapedPattern = preg_quote($this->pattern, Delimiter::REGEX);

        // Create a valid regex by enclosing in delimiters
        $regex = Delimiter::REGEX. $escapedPattern . Delimiter::REGEX;

        if (!preg_match($regex, $value)) {
            $msg = "'$propName' does not satisfy the pattern '$this->pattern'";
        }
        return $msg;
    }

    #[\Override]
    protected function getConstraint(): string {
        return 'is pattern ' . $this->pattern;
    }
}
