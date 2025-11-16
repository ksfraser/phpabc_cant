<?php
require_once 'vendor/autoload.php';

use Ksfraser\PhpabcCanntaireachd\TokenDictionary;

$dict = new TokenDictionary();
$allTokens = $dict->getAllTokens();

echo "Total tokens loaded: " . count($allTokens) . "\n";

if (count($allTokens) > 0) {
    echo "First 5 tokens:\n";
    $i = 0;
    foreach ($allTokens as $key => $token) {
        if ($i >= 5) break;
        echo "  '$key' => '" . ($token['cannt_token'] ?? 'null') . "'\n";
        $i++;
    }

    // Test specific conversions
    $testKeys = ['{g}A', 'A', 'B', '{g}c'];
    foreach ($testKeys as $key) {
        $result = $dict->convertAbcToCannt($key);
        echo "convertAbcToCannt('$key') = '" . ($result ?? 'null') . "'\n";
    }
} else {
    echo "No tokens loaded!\n";
}