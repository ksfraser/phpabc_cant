<?php
require __DIR__ . '/../vendor/autoload.php';
$path = __DIR__ . '/../test-multi.abc';
if (!file_exists($path)) {
    echo "MISSING FILE: $path\n";
    exit(1);
}
$content = file_get_contents($path);

// Load token dictionary for canntaireachd processing
$dict = new \Ksfraser\PhpabcCanntaireachd\TokenDictionary();
// Try to load legacy abc_dict.php if available
$legacyPath = __DIR__ . '/../src/Ksfraser/PhpabcCanntaireachd/abc_dict.php';
if (file_exists($legacyPath)) {
    // include inside isolated scope to avoid leaking symbols
    $abc = [];
    try {
        include $legacyPath; // populates $abc in many legacy files
    } catch (\Throwable $e) {
        // ignore
    }
    if (!empty($abc) && is_array($abc)) {
        $pre = [];
        foreach ($abc as $k => $v) {
            $pre[$k] = [
                'cannt_token' => $v['cannt'] ?? ($v['cannt_token'] ?? null),
                'bmw_token' => $v['bmw'] ?? null,
                'description' => $v['desc'] ?? null,
            ];
        }
        $dict->prepopulate($pre);
    }
}

try {
    $result = \Ksfraser\PhpabcCanntaireachd\AbcProcessor::process($content, $dict);
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
