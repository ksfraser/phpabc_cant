#!/usr/bin/env php
<?php
/**
 * CLI launcher for ABC Canntaireachd tools
 *
 * Usage:
 *   php bin/abc-cannt-cli.php --file=path/to/file.abc [--convert]
 *
 * Options:
 *   --file, -f      Path to ABC file to process
 *   --convert, -c   Add canntaireachd lines to melody/bagpipe voices
 */
require __DIR__ . '/../vendor/autoload.php';

use Ksfraser\PhpabcCanntaireachd\AbcParser;
use Ksfraser\PhpabcCanntaireachd\AbcValidator;

$options = getopt('f:c', ['file:', 'convert']);
$file = $options['f'] ?? $options['file'] ?? null;
$convert = isset($options['c']) || isset($options['convert']);

if (!$file || !file_exists($file)) {
    fwrite(STDERR, "ABC file not found.\n");
    exit(1);
}

$abcContent = file_get_contents($file);
$validator = new AbcValidator();
$errors = $validator->validate($abcContent);

if ($errors) {
    echo "Validation errors found in '$file':\n";
    foreach ($errors as $err) {
        echo "  - $err\n";
    }
    exit(2);
}

$parser = new AbcParser();
$parser->process($abcContent);

// Example validation output
// (Replace with real validation logic)
echo "File '$file' processed and validated.\n";
if ($convert) {
    echo "[Example] Would add canntaireachd lines to melody/bagpipe voices.\n";
}
// Example: Compare canntaireachd lines if present
// echo "[Example] Would compare existing canntaireachd lines and report differences.\n";
