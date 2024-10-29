<?php
namespace App\Core\Http\Middleware\Traits;

trait ManagesMiddlewares
{
    use HasMiddlewares;
    use ExcludingMiddlewares;
}
