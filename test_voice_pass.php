<?php
require 'vendor/autoload.php';

$lines = file('test-Suo.abc', FILE_IGNORE_NEW_LINES);

echo "Input lines with [V:M]:\n";
foreach($lines as $i => $line) {
    if(preg_match('/\[V:M\]/i', $line)) {
        echo "Line $i: $line\n";
    }
}

$pass = new Ksfraser\PhpabcCanntaireachd\AbcVoicePass();
$result = $pass->process($lines);

echo "\nLooking for Bagpipes voice in output...\n";
$found = false;
foreach($result as $i => $line) {
    if(stripos($line, 'V:Bagpipes') !== false) {
        echo "Line $i: $line\n";
        for($j = $i; $j < min($i+5, count($result)); $j++) {
            echo "  $j: {$result[$j]}\n";
        }
        $found = true;
    }
}

if (!$found) {
    echo "No V:Bagpipes found!\n";
}
