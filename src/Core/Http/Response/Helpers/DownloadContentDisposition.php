<?php
namespace App\Core\Http\Response\Helpers;

class DownloadContentDisposition implements ContentDisposition
{
    public function __construct(private readonly string $downloadedFilename)
    {
        
    }

    #[\Override]
    public function value(): string {
        return 'attachment; filename="' . $this->downloadedFilename . '"';
    }
}
