<?php
declare(strict_types=1);

namespace App;

require_once __DIR__ . '/vendor/autoload.php';

use App\Constants\ErrorMessage;
use App\Constants\HttpCode;
use App\Support\Log\Logger;

try {
    $app = AppProvider::get();
    $app->run(fn() => abort('404 Not Found'));
}
catch (\Throwable $e) {
    if (!isProduction()) {
        throw $e;
    }

    $errorMsg = $e->getMessage();
    $container = $app->container();

    if ($container->isBound(Logger::class)) {
        $logger = $container->get(Logger::class);
        $logger->error($errorMsg);
    }
    else {
        error_log($errorMsg);
    }

    abort(ErrorMessage::INTERNAL_SERVER_ERROR, HttpCode::INTERNAL_SERVER_ERROR);
}
