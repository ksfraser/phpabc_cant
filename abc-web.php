<?php
/**
 * Standalone web UI entry point for the ABC Canntaireachd Converter.
 *
 * Usage:
 *   php -S localhost:8080
 *   Then open http://localhost:8080/abc-web.php
 *
 * Or run directly:
 *   php abc-web.php
 */

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use ksfraser\PhpabcCanntaireachd\Ui\AbcUiRouter;

$router = new AbcUiRouter();
$router->route();