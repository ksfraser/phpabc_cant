<?php
$abc = [];
include 'src/Ksfraser/phpabc_canntaireachd/abc_dict.php';
echo 'Loaded ' . count($abc) . ' entries' . PHP_EOL;
echo 'A: ' . ($abc['A']['cannt'] ?? 'not found') . PHP_EOL;
echo '{g}A: ' . ($abc['{g}A']['cannt'] ?? 'not found') . PHP_EOL;