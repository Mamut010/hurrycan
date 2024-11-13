<?php
namespace App\Support\DateTime;

use App\Constants\Format;

class JsonSerializableDateTime extends \DateTime implements \JsonSerializable
{
    #[\Override]
    public function jsonSerialize(): mixed {
        return $this->format(Format::ISO_8601_DATE);
    }
}
