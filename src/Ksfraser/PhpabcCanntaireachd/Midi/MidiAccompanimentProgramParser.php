<?php
namespace Ksfraser\PhpabcCanntaireachd\Midi;

use Ksfraser\PhpabcCanntaireachd\AbcMidiLine;
use Ksfraser\PhpabcCanntaireachd\AbcLineParser;

/**
 * Parser for %%MIDI chordprog and bassprog directives
 */
class MidiAccompanimentProgramParser implements AbcLineParser
{
    /**
     * @param mixed $line
     * @return bool
     */
    public function canParse($line) {
        return preg_match('/^%%MIDI\s+(chordprog|bassprog)\s+/i', trim($line));
    }

    /**
     * @param mixed $line
     * @param mixed $tune
     * @return bool
     */
    public function parse($line, $tune) {
        if (!preg_match('/^%%MIDI\s+(chordprog|bassprog)\s+(\d+)/i', trim($line), $matches)) {
            return false;
        }

        $type = $matches[1]; // 'chordprog' or 'bassprog'
        $program = (int)$matches[2];

        // Validate program range (1-128 for General MIDI)
        if ($program < 1 || $program > 128) {
            // Invalid program - add as comment for user awareness
            $tune->add(new AbcMidiLine("%% Invalid MIDI $type: $program (must be 1-128)"));
            return true;
        }

        // Create corrected MIDI program line
        $correctedLine = "%%MIDI $type $program";
        $tune->add(new AbcMidiLine($correctedLine));
        return true;
    }

    /**
     * @param mixed $line
     * @return bool
     */
    public function validate($line) {
        if (!preg_match('/^%%MIDI\s+(chordprog|bassprog)\s+(\d+)$/i', trim($line), $matches)) {
            return false;
        }

        $program = (int)$matches[2];
        return $program >= 1 && $program <= 128;
    }
}
