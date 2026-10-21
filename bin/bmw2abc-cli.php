#!/usr/bin/env php
<?php
/**
 * BMW to ABC CLI Tool
 *
 * Converts a Bagpipe Music Writer file into ABC notation.
 *
 * The BMW file has a header block of its own, a record for the title, the
 * type, the composer and the foot, and then the music in BMW's notation. Each
 * of its tokens is looked up in the BMW dictionary, longest match first, so
 * that a doubling is not taken apart by a second pass.
 *
 * A token that is not in the dictionary is left as it was, so that nothing is
 * lost.
 *
 * Usage:
 *   php bmw2abc-cli.php --file <file.bmw> [options]
 *
 * Options:
 *   -f, --file <file.bmw>   The BMW file to convert (may be repeated)
 *   -o, --output <file>     Where to write the ABC (default: the name of the
 *                            BMW file with the ".abc" extension)
 *       --suffix <text>     The suffix of the written file (default: none)
 *       --force             Write the file even when it exists
 *   -h, --help              Show this help message
 *   -v, --verbose           Show the ABC on the screen as well
 *
 * Examples:
 *   php bmw2abc-cli.php --file "9O_Clock_Walk.bmw"
 *   php bmw2abc-cli.php --file "9O_Clock_Walk.bmw" --output walk.abc --verbose
 *
 * @package ksfraser\PhpabcCanntaireachd
 *
 * @since 2.2.0
 */

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use ksfraser\PhpabcCanntaireachd\Bmw\BmwToAbcConverter;

$options = getopt('f:o:hv', ['file:', 'output:', 'suffix:', 'force', 'help', 'verbose']);

if (isset($options['h']) || isset($options['help'])) {
    showUsage();
    exit(0);
}

$files = [];
foreach ((array)($options['f'] ?? $options['file'] ?? []) as $file) {
    $files[] = (string)$file;
}

if ($files === []) {
    fwrite(STDERR, "Error: nothing to do; give --file\n");
    showUsage();
    exit(1);
}

$output = isset($options['o']) || isset($options['output'])
    ? (string)($options['o'] ?? $options['output'])
    : null;
$suffix = (string)($options['suffix'] ?? '');
$force = isset($options['force']);
$verbose = isset($options['v']) || isset($options['verbose']);

$converter = new BmwToAbcConverter();
$written = 0;

foreach ($files as $file) {
    if (!is_file($file)) {
        fwrite(STDERR, "Error: '$file' does not exist\n");
        exit(1);
    }
    $content = file_get_contents($file);
    if ($content === false) {
        fwrite(STDERR, "Error: could not read '$file'\n");
        exit(1);
    }

    $abc = $converter->convert($content);

    if ($verbose) {
        echo $abc, "\n";
    }

    $target = $output ?? defaultOutputName($file, $suffix);
    if (!$force && file_exists($target)) {
        fwrite(STDERR, "Error: '$target' already exists; use --force to write it\n");
        exit(1);
    }
    file_put_contents($target, rtrim($abc, "\n") . "\n");
    $written++;
    echo "wrote ", $target, "\n";
}

printf("files converted : %d\n", $written);
exit(0);

/**
 * The name of the ABC file: the name of the BMW file with the ".abc"
 * extension.
 */
function defaultOutputName(string $file, string $suffix): string
{
    $directory = dirname($file);
    $stem = pathinfo($file, PATHINFO_FILENAME);
    return $directory . '/' . $stem . $suffix . '.abc';
}

function showUsage(): void
{
    $script = basename($argv[0]);
    echo <<<USAGE
    BMW to ABC CLI Tool

    Converts a Bagpipe Music Writer file into ABC notation. Each BMW token is
    looked up in the BMW dictionary, longest match first, and a token that is
    not in the dictionary is left as it was.

    Usage:
      php $script --file <file.bmw> [options]

    Options:
      -f, --file <file.bmw>   The BMW file to convert (may be repeated)
      -o, --output <file>     Where to write the ABC (default: the name of the
                              BMW file with the ".abc" extension)
          --suffix <text>     The suffix of the written file (default: none)
          --force             Write the file even when it exists
      -h, --help              Show this help message
      -v, --verbose           Show the ABC on the screen as well

    Examples:
      php $script --file "9O_Clock_Walk.bmw"
      php $script --file "9O_Clock_Walk.bmw" --output walk.abc --verbose

    USAGE;
}
