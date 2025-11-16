<?php
require_once 'src/Ksfraser/PhpabcCanntaireachd/TokenDictionary.php';
$dict = new Ksfraser\PhpabcCanntaireachd\TokenDictionary();
$tokens = $dict->getAllTokens();
echo 'Loaded tokens: ' . count($tokens) . PHP_EOL;
foreach ($tokens as $key => $val) {
    if (strpos($key, '{g}A') !== false) {
        echo "Key: '" . $key . "' (len=" . strlen($key) . ") => " . var_export($val, true) . PHP_EOL;
    }
}
$testKey = '{g}A';
echo "Test key: '" . $testKey . "' (len=" . strlen($testKey) . ")\n";
echo "Direct lookup for '$testKey': " . var_export($dict->convertAbcToCannt($testKey), true) . PHP_EOL;
if (isset($tokens[$testKey])) {
    echo "isset tokens['$testKey']: " . var_export($tokens[$testKey], true) . PHP_EOL;
} else {
    echo "tokens['$testKey'] not set\n";
}
?>