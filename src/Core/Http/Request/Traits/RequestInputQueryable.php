<?php

namespace App\Core\Http\Request\Traits;

use App\Utils\Arrays;

trait RequestInputQueryable
{
    abstract public function inputs(): array;

    /**
     * @param string|string[] $names
     */
    public function only(string|array $names, string ...$others): array {
        if (is_string($names)) {
            $names = [$names];
        }
        array_push($names, ...$others);
        return array_intersect($this->inputs(), $names);
    }

    /**
     * @param string|string[] $names
     */
    public function except(string|array $names, string ...$others): array {
        if (is_string($names)) {
            $names = [$names];
        }
        array_push($names, ...$others);
        return Arrays::keysExcludeByNames($this->inputs(), $names);
    }

    public function has(string|array $name): bool {
        $inputs = $this->inputs();
        $names = Arrays::asArray($name);
        foreach ($names as $checked) {
            if (!array_key_exists($checked, $inputs)) {
                return false;
            }
        }
        return true;
    }

    public function missing(string $name): bool {
        return !$this->has($name);
    }

    public function hasAny(array $names): bool {
        $inputs = $this->inputs();
        foreach ($names as $checked) {
            if (array_key_exists($checked, $inputs)) {
                return true;
            }
        }
        return false;
    }

    public function input(string $name, mixed $default = null): mixed {
        return Arrays::getOrDefaultExists($this->inputs(), $name, $default);
    }

    public function string(string $name, string $default = ''): string {
        $value = $this->input($name, $default);
        return strval($value);
    }

    public function integer(string $name, int $default = 0): int {
        $value = $this->input($name, $default);
        return is_int($value) ? $value : $default;
    }

    public function boolean(string $name, bool $default = false): bool {
        $value = $this->input($name, $default);
        return boolval($value);
    }
}
