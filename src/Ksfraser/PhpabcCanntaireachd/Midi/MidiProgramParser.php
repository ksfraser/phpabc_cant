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
    public function canParse(string $line): bool {
        return preg_match('/^%%MIDI\s+program\s+/i', trim($line));
    }

    public function parse(string $line, AbcTune $tune): bool {
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

    public function validate(string $line): bool {
        // Valid if it matches the expected format
        return preg_match('/^%%MIDI\s+program\s+\d+(?:\s+%.*)?$/i', trim($line));
    }
}
