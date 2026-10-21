#!/usr/bin/env php
<?php
/**
 * ABC Tune Cleanup CLI Tool
 *
 * Tidies up the ABC files of a pipe band score:
 *
 *  - the header fields are written in the same order in every tune;
 *  - the tune starts with the standard include block, so that the MIDI
 *    settings, the fonts and the percussion maps are shared;
 *  - every voice says what it is and how it is written: the name, the short
 *    name, the stems, the clef and the transposition;
 *  - the pipe band drums are on their one line percussion staff, and on the two
 *    notes that staff has;
 *  - the bagpipe part is checked against the pipe scale;
 *  - the bars are laid out again, four to a line, with the "$" line ending
 *    marker, and every line says which voice and which meter it is.
 *
 * The cleaned tune is written to a new file: the name of the tune with "_php"
 * added before the ".abc". The tune that was read is never changed.
 *
 * Usage:
 *   php abc-tune-cleanup-cli.php --file <abcfile> [options]
 *   php abc-tune-cleanup-cli.php --dir <directory> [options]
 *
 * Options:
 *   -f, --file <abcfile>      The ABC file to clean up (may be repeated)
 *   -d, --dir <directory>     Clean up every ABC file in the directory
 *   -o, --output-dir <dir>    Where to write the cleaned files (default: with
 *                              the file that was read)
 *   -i, --include-root <dir>   Where the include files live (default: the
 *                              current directory)
 *       --suffix <text>       The suffix of the cleaned file (default: _php)
 *       --dry-run             Report what would change, write nothing
 *   -h, --help                Show this help message
 *   -v, --verbose             Show the report of every file
 *
 * Examples:
 *   php abc-tune-cleanup-cli.php --file "Scores/Marches/2_4/TheBlackBear.abc"
 *   php abc-tune-cleanup-cli.php --dir Scores --dry-run
 *
 * @package ksfraser\PhpabcCanntaireachd
 *
 * @since 2.2.0
 */

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use ksfraser\PhpabcCanntaireachd\Cleanup\BarReflower;
use ksfraser\PhpabcCanntaireachd\Cleanup\HeaderOrderNormaliser;
use ksfraser\PhpabcCanntaireachd\Cleanup\IncludeBlockWriter;
use ksfraser\PhpabcCanntaireachd\Cleanup\InstrumentProfileRegistry;
use ksfraser\PhpabcCanntaireachd\Cleanup\Parser\AbcDocumentParser;
use ksfraser\PhpabcCanntaireachd\Cleanup\VoiceAttributeNormaliser;
use ksfraser\PhpabcCanntaireachd\Cleanup\TuneCleanupPipeline;

$options = getopt('f:d:o:i:hvl', [
    'file:', 'dir:', 'output-dir:', 'include-root:', 'suffix:', 'dry-run', 'help', 'verbose', 'list-voices',
]);

if (isset($options['h']) || isset($options['help'])) {
    showUsage();
    exit(0);
}

$files = [];
foreach ((array)($options['f'] ?? $options['file'] ?? []) as $file) {
    $files[] = (string)$file;
}
$directory = $options['d'] ?? $options['dir'] ?? null;
if ($directory !== null) {
    $directory = (string)$directory;
    if (!is_dir($directory)) {
        fwrite(STDERR, "Error: '$directory' is not a directory\n");
        exit(1);
    }
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));
    foreach ($iterator as $entry) {
        if ($entry->isFile() && strtolower($entry->getExtension()) === 'abc') {
            $files[] = $entry->getPathname();
        }
    }
}

if ($files === []) {
    fwrite(STDERR, "Error: nothing to do; give --file or --dir\n");
    showUsage();
    exit(1);
}

$outputDir = $options['o'] ?? $options['output-dir'] ?? null;
$outputDir = $outputDir === null ? null : (string)$outputDir;
$includeRoot = $options['i'] ?? $options['include-root'] ?? getcwd();
$includeRoot = (string)$includeRoot;
$suffix = $options['suffix'] ?? '_php';
$suffix = (string)$suffix;
$dryRun = isset($options['dry-run']);
$verbose = isset($options['v']) || isset($options['verbose']);

$parser = new AbcDocumentParser();
$pipeline = new TuneCleanupPipeline(
    headerOrder: new HeaderOrderNormaliser(),
    includes: new IncludeBlockWriter($includeRoot),
    voiceNormaliser: new VoiceAttributeNormaliser(new InstrumentProfileRegistry()),
    reflower: new BarReflower()
);

$report = [];
$written = 0;
$failed = 0;

foreach ($files as $file) {
    if (!is_file($file)) {
        fwrite(STDERR, "Error: '$file' does not exist\n");
        $failed++;
        continue;
    }

    $document = $parser->parseFile($file);
    $tuneDirectory = $outputDir ?? dirname($file);
    $result = $pipeline->cleanup($document, $tuneDirectory);

    $entries = $result['report'];
    $report = array_merge($report, $entries);

    if ($verbose || $entries !== []) {
        echo basename($file), "\n";
        foreach ($entries as $entry) {
            echo '  [', $entry['kind'], '] ', $entry['message'], "\n";
        }
        if ($entries === []) {
            echo "  nothing to change\n";
        }
    }

    if ($dryRun) {
        continue;
    }

    $lines = [];
    foreach ($result['document']->getIncludes() as $line) {
        $lines[] = $line;
    }
    foreach ($result['document']->getPreamble() as $line) {
        $lines[] = $line;
    }
    $separators = $result['document']->getSeparators();
    foreach ($result['document']->getTunes() as $index => $tune) {
        foreach ($tune->render($pipeline->getHeaderOrder()) as $line) {
            $lines[] = $line;
        }
        if (isset($separators[$index])) {
            foreach ($separators[$index] as $line) {
                $lines[] = $line;
            }
        }
    }

    $target = $tuneDirectory . '/' . outputName(basename($file), $suffix);
    if (!is_dir($tuneDirectory)) {
        fwrite(STDERR, "Error: the output directory '$tuneDirectory' does not exist\n");
        $failed++;
        continue;
    }
    file_put_contents($target, implode("\n", $lines) . "\n");
    $written++;
    echo "wrote ", $target, "\n";
}

echo "\n";
printf("files processed : %d\n", count($files));
printf("files written   : %d\n", $written);
printf("problems found  : %d\n", count($report));
if ($dryRun) {
    echo "dry run: no file was written\n";
}
exit($failed > 0 ? 1 : 0);

/**
 * The name of the cleaned file: the name of the tune with the suffix added
 * before the ".abc".
 */
function outputName(string $name, string $suffix): string
{
    $stem = pathinfo($name, PATHINFO_FILENAME);
    $extension = pathinfo($name, PATHINFO_EXTENSION);
    return $extension === '' ? $stem . $suffix : $stem . $suffix . '.' . $extension;
}

function showUsage(): void
{
    $script = basename($argv[0]);
    echo <<<USAGE
    ABC Tune Cleanup CLI Tool

    Tidies up the ABC files of a pipe band score: the header fields in one
    order, the standard include block, the voice definitions, the pipe band
    drums on their one line staff, the bagpipe part checked against the pipe
    scale, and the bars laid out four to a line.

    The cleaned tune is written to a new file: the name of the tune with
    "_php" added before the ".abc". The tune that was read is never changed.

    Usage:
      php $script --file <abcfile> [options]
      php $script --dir <directory> [options]

    Options:
      -f, --file <abcfile>      The ABC file to clean up (may be repeated)
      -d, --dir <directory>     Clean up every ABC file in the directory
      -o, --output-dir <dir>    Where to write the cleaned files (default: with
                                the file that was read)
      -i, --include-root <dir>   Where the include files live (default: the
                                current directory)
          --suffix <text>       The suffix of the cleaned file (default: _php)
          --dry-run             Report what would change, write nothing
      -h, --help                Show this help message
      -v, --verbose             Show the report of every file

    Examples:
      php $script --file "Scores/Marches/2_4/TheBlackBear.abc"
      php $script --dir Scores --dry-run

    USAGE;
}
