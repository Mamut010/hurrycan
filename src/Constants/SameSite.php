<?php
namespace App\Constants;

enum SameSite: string
{
    case NONE = 'None';
    case LAX = 'Lax';
    case STRICT = 'Strict';

    public function formatToSend(): string {
        if ($this === SameSite::NONE) {
            /* Hack to make CHIPS: https://github.com/php/php-src/issues/12646 */
            return $this->value . '; Partitioned';
        }
        else {
            return $this->value;
        }
    }
}
