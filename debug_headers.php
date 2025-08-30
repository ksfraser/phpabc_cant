<?php
require __DIR__ . '/vendor/autoload.php';
use Ksfraser\PhpabcCanntaireachd\AbcFileParser;
$p = new AbcFileParser();
$tunes = $p->parse("X:1\nT:Test Tune\n");
if (!$tunes) { echo "no tunes\n"; exit(0); }
$t = array_values($tunes)[0];
$headers = $t->getHeaders();
foreach ($headers as $k => $h) {
    echo "$k: " . (method_exists($h,'get') ? $h->get() : '(no get)') . "\n";
}
