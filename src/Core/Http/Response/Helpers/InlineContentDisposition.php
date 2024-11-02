<?php
namespace App\Core\Http\Response\Helpers;

class InlineContentDisposition implements ContentDisposition
{
    #[\Override]
    public function value(): string {
        return 'inline';
    }
}
