<?php
namespace Ksfraser\PhpabcCanntaireachd\Midi;

use Ksfraser\PhpabcCanntaireachd\AbcLineParser;
use Ksfraser\PhpabcCanntaireachd\AbcTune;
use Ksfraser\PhpabcCanntaireachd\AbcMidiLine;

/**
 * Parser for %%MIDI gracedivider directives
 */
class MidiGraceDividerParser implements AbcLineParser
{
    /**
     * @param mixed $line
     * @return bool
     */
    public function canParse($line) {
        return preg_match('/^%%MIDI\s+gracedivider\s+/i', trim($line));
    }

    /**
     * @param mixed $line
     * @param mixed $tune
     * @return bool
     */
    public function parse($line, $tune) {
        if (!preg_match('/^%%MIDI\s+gracedivider\s+(\d+)(?:\s+(.+))?/i', trim($line), $matches)) {
            return false;
        }

        $divider = (int)$matches[1];
        $comment = isset($matches[2]) ? trim($matches[2]) : '';

        // Validate divider (should be positive)
        if ($divider <= 0) {
            $divider = 4; // Default
        }

        $correctedLine = "%%MIDI gracedivider $divider";
        if (!empty($comment)) {
            $correctedLine .= " % $comment";
        }

        $tune->add(new AbcMidiLine($correctedLine));
        return true;
    }

    /**
     * @param mixed $line
     * @return bool
     */
    public function validate($line) {
        return preg_match('/^%%MIDI\s+gracedivider\s+(\d+)(?:\s+%.*)?$/i', trim($line), $matches) &&
               $matches[1] > 0;
    }
}
