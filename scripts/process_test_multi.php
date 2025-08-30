<?php
require __DIR__ . '/../vendor/autoload.php';
$path = __DIR__ . '/../test-multi.abc';
if (!file_exists($path)) {
    echo "MISSING FILE: $path\n";
    exit(1);
}
$content = file_get_contents($path);
try {
    $result = \Ksfraser\PhpabcCanntaireachd\AbcProcessor::process($content, []);
    if (isset($result['lines']) && is_array($result['lines'])) {
        echo implode(PHP_EOL, $result['lines']) . PHP_EOL;
    } else {
        var_export($result);
        echo PHP_EOL;
    }
} catch (Throwable $e) {
    echo "ERROR: " . $e->getMessage() . PHP_EOL;
    echo $e->getTraceAsString() . PHP_EOL;
    exit(1);
}
