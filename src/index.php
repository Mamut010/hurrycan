<?php
declare(strict_types=1);

namespace App;

require_once __DIR__ . '/vendor/autoload.php';

try {
    AppProvider::get()->run(fn() => abort('404 Not Found'));
}
catch (\Throwable $e) {
    if (isProduction()) {
        error_log($e->getMessage());
        abort('500 Internal Server Error', \App\Constants\HttpCode::INTERNAL_SERVER_ERROR);
    }
    else {
        throw $e;
    }
}
