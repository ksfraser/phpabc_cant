<?php

require_once 'vendor/autoload.php';

use Ksfraser\PhpabcCanntaireachd\AbcNote;
use Ksfraser\PhpabcCanntaireachd\AbcParser;
use ksfraser\origin\KsfFile;

// Example usage of the PSR-4 compliant classes

// Create an ABC note
$note = new AbcNote('G', '', '', '1');
echo "ABC Note output: " . $note->get_body_out() . "\n";

// Use the ksf-file dependency
$file = new KsfFile("example.txt", __DIR__);
echo "KsfFile created for: example.txt\n";

echo "PSR-4 autoloading is working correctly!\n";
