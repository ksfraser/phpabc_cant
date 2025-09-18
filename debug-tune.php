<?php
require __DIR__ . '/vendor/autoload.php';

$abcContent = 'X:1
T:Test
K:HP
|A B C D|';

$parser = new \Ksfraser\PhpabcCanntaireachd\AbcFileParser();
$tunes = $parser->parse($abcContent);

echo "Number of tunes: " . count($tunes) . "\n";

if (count($tunes) > 0) {
    $tune = $tunes[0];
    echo "Tune subitems: " . count($tune->getLines()) . "\n";

    foreach ($tune->getLines() as $i => $line) {
        echo "Line $i: " . get_class($line) . "\n";
        echo "  Render: '" . trim($line->render()) . "'\n";
    }

    echo "Full tune render:\n";
    echo $tune->render();
}
