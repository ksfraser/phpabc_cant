<?php
namespace Ksfraser\PhpabcCanntaireachd\Midi;

use Ksfraser\PhpabcCanntaireachd\AbcLineParser;
use Ksfraser\PhpabcCanntaireachd\AbcMidiLine;
use Ksfraser\PhpabcCanntaireachd\AbcTune;

/**
 * Parser for %%MIDI program directives
 */
class MidiProgramParser implements AbcLineParser
{
    /**
     * @param mixed $line
     * @return bool
     */
    public function canParse($line) {
        return preg_match('/^%%MIDI\s+program\s+/i', trim($line));
    }

    /**
     * @param mixed $line
     * @param mixed $tune
     * @return bool
     */
    public function parse($line, $tune) {
        if (!preg_match('/^%%MIDI\s+program\s+(\d+)(?:\s+(.+))?/i', trim($line), $matches)) {
            return false;
        }

        $program = (int)$matches[1];
        $comment = isset($matches[2]) ? trim($matches[2]) : '';

        // Clean up malformed comments (remove # prefix if present)
        if (preg_match('/^#(.+)/', $comment, $commentMatches)) {
            $comment = trim($commentMatches[1]);
        }

        // Create corrected MIDI program line
        $correctedLine = "%%MIDI program $program";
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
        // Valid if it matches the expected format
        return preg_match('/^%%MIDI\s+program\s+\d+(?:\s+%.*)?$/i', trim($line));
    }
}
