<?php
namespace App\Support;

use App\Utils\Arrays;
use ArrayAccess;

class OptionsBase implements ArrayAccess
{
    /**
     * @param ?array<string,mixed> $args
     */
    public function __construct(?array $args = null) {
        if ($args === null) {
            return;
        }

        $props = $this->getCorrectSignatureProps();
        foreach ($props as $prop) {
            $propName = $prop->getName();
            if (array_key_exists($propName, $args)) {
                $prop->setValue($this, $args[$propName]);
            }
        }
    }

    /**
     * @return \ReflectionProperty[]
     */
    private static function getCorrectSignatureProps() {
        $reflector = new \ReflectionClass(static::class);
        $props = $reflector->getProperties();
        return Arrays::filterReindex(
            $props,
            fn (\ReflectionProperty $prop) => static::isCorrectSignatureProp($prop)
        );
    }

    private static function isCorrectSignatureProp(\ReflectionProperty $prop) {
        return $prop->isPublic() && !$prop->isStatic();
    }

    /**
     * Return this Options as an associative array with all non-null props.
     * @return array<string,mixed> The associative array with keys being the non-null prop, values
     *                             being the prop's representative values.
     */
    public function toArray(): array {
        $props = static::getCorrectSignatureProps();
        $result = [];
        foreach ($props as $prop) {
            if (!$prop->isInitialized($this)) {
                continue;
            }
            $value = $prop->getValue($this);
            if ($value !== null && !is_callable($value)) {
                $propName = $prop->getName();
                $representativeValue = $this->propToRepresentativeValue($propName, $value);
                if ($representativeValue !== null) {
                    $result[$propName] = $representativeValue;
                }
            }
        }
        return $result;
    }

    /**
     * Subclasses may override this method to correctly convert a property value to a desired
     * reprentative value or avoid converting a property based on certain condition by returning null.\
     * By default, this method just returns all values as is.
     */
    protected function propToRepresentativeValue(string $propName, mixed $propValue) {
        return $propValue;
    }

    /**
     * Return all name of the options defined in the class.
     * @return string[] Option's names defined in the class.
     */
    public static function getOptions(): array {
        $props = static::getCorrectSignatureProps();
        return array_map(fn (\ReflectionProperty $prop) => $prop->getName(), $props);
    }

    public function offsetExists(mixed $offset): bool {
        return isset($this->{$offset});
    }

    public function offsetGet(mixed $offset): mixed {
        return $this->offsetExists($offset) ? $this->{$offset} : null;
    }

    public function offsetSet(mixed $offset, mixed $value): void {
        if (is_null($offset) || !$this->offsetExists($offset)) {
            return;
        }
        $this->{$offset} = $value;
    }

    public function offsetUnset(mixed $offset): void {
        $this->offsetSet($offset, null);
    }
}
