<?php
namespace App\Http\Middlewares;

use App\Core\Http\Middleware\Middleware;
use App\Core\Http\Request\Request;
use App\Core\Http\Response\Response;
use App\Settings\BcSetting;
use Closure;

class BcSetupMiddleware implements Middleware
{
    #[\Override]
    public function handle(Request $request, Closure $next): Response
    {
        bcscale(BcSetting::SCALE);
        return $next();
    }
}
