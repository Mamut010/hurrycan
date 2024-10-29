<?php
declare(strict_types=1);

namespace App;

require_once __DIR__ . '/vendor/autoload.php';

try {
    AppProvider::get()->run(fn() => abort('404 Not Found'));
}
catch (\Exception $e) {
    if (isProduction()) {
        error_log($e->getMessage());
        abort('Internal Server Error', \App\Constants\HttpCode::INTERNAL_SERVER_ERROR);
    }
    else {
        throw $e;
    }
}
