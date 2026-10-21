#!/usr/bin/env php
<?php
/**
 * Bagpipe Fingering Format File CLI Tool
 *
 * Writes the abc2ps format file that draws the fingering of the Highland
 * chanter, and the words for the embellishments.
 *
 * The fingering table and the words come from the project, so the file and
 * the token dictionary cannot drift apart. Write the file again whenever the
 * dictionary changes.
 *
 * Usage:
 *   php abc-bagpipe-fingering-cli.php [options]
 *
 * Options:
 *   -o, --output <file>   Where to write the format file
 *                         (default: bagpipe_fingering.fmt in this directory)
 *       --print           Write the file to the screen instead
 *   -h, --help            Show this help message
 *
 * Examples:
 *   php abc-bagpipe-fingering-cli.php --output bagpipe_fingering.fmt
 *   php abc-bagpipe-fingering-cli.php --print | head -40
 *
 * @package ksfraser\PhpabcCanntaireachd
 *
 * @since 2.2.0
 */

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use ksfraser\PhpabcCanntaireachd\Bagpipe\BagpipeFingering;
use ksfraser\PhpabcCanntaireachd\Bagpipe\BagpipeFingeringWriter;

$options = getopt('o:h', ['output:', 'print', 'help']);

if (isset($options['h']) || isset($options['help'])) {
    showUsage();
    exit(0);
}

$writer = new BagpipeFingeringWriter(new BagpipeFingering());
$content = $writer->render();

if (isset($options['print'])) {
    echo $content;
    exit(0);
}

$output = (string)($options['o'] ?? $options['output'] ?? getcwd() . '/bagpipe_fingering.fmt');
file_put_contents($output, $content);
printf("wrote %s (%d bytes)\n", $output, strlen($content));
exit(0);

function showUsage(): void
{
    $script = basename($argv[0]);
    echo <<<USAGE
    Bagpipe Fingering Format File CLI Tool

    Writes the abc2ps format file that draws the fingering of the Highland
    chanter, and the words for the embellishments.

    The file gives one decoration for each note of the pipe scale:
    !pipeG! is the low G, !pipeg! the G above it. The fingering is drawn as
    the chanter with its holes closed as the fingers close them.

    Use it with:
      abcm2ps -F bagpipe_fingering.fmt tune.abc
    or, in a tune that uses the include files:
      %%abc-include "bagpipe_fingering.fmt"

    Usage:
      php $script [options]

    Options:
      -o, --output <file>   Where to write the format file
                            (default: bagpipe_fingering.fmt in this directory)
          --print           Write the file to the screen instead
      -h, --help            Show this help message

    USAGE;
}
