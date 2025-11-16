<?php
$abc = [];
try {
    include 'src/Ksfraser/phpabc_canntaireachd/abc_dict.php';
} catch (\Throwable $e) {
    echo "Error: " . $e->getMessage() . PHP_EOL;
}
echo 'abc array keys: ' . count($abc) . PHP_EOL;
if (isset($abc['{g}A'])) {
    echo "abc['{g}A']: " . var_export($abc['{g}A'], true) . PHP_EOL;
} else {
    echo "abc['{g}A'] not set\n";
}
?>