<?php
require __DIR__ . '/vendor/autoload.php';

$abcContent = file_get_contents('test_midi_directives.abc');

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

    echo "\nFull tune render:\n";
    echo $tune->render();
}
