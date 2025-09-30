<?php
require_once __DIR__ . '/src/Ksfraser/PhpabcCanntaireachd/TokenMappingHelpers.php';

try {
    Ksfraser\PhpabcCanntaireachd\TokenNormalizer::normalize("");
    echo "NO EXCEPTION\n";
} catch (Throwable $e) {
    echo get_class($e) . ': ' . $e->getMessage() . "\n";
}
