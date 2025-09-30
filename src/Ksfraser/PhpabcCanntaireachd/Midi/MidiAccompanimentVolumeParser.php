<?php
namespace Ksfraser\PhpabcCanntaireachd\Midi;

use Ksfraser\PhpabcCanntaireachd\AbcMidiLine;
use Ksfraser\PhpabcCanntaireachd\AbcLineParser;

/**
 * Parser for %%MIDI chordvol and bassvol directives
 */
class MidiAccompanimentVolumeParser implements AbcLineParser
{
    /**
     * @param mixed $line
     * @return bool
     */
    public function canParse($line) {
        return preg_match('/^%%MIDI\s+(chordvol|bassvol)\s+/i', trim($line));
    }

    /**
     * @param mixed $line
     * @param mixed $tune
     * @return bool
     */
    public function parse($line, $tune) {
        if (!preg_match('/^%%MIDI\s+(chordvol|bassvol)\s+(\d+)/i', trim($line), $matches)) {
            return false;
        }

        $type = $matches[1]; // 'chordvol' or 'bassvol'
        $volume = (int)$matches[2];

        // Validate volume range (0-127 for MIDI)
        if ($volume < 0 || $volume > 127) {
            // Invalid volume - add as comment for user awareness
            $tune->add(new AbcMidiLine("%% Invalid MIDI $type: $volume (must be 0-127)"));
            return true;
        }

        // Create corrected MIDI volume line
        $correctedLine = "%%MIDI $type $volume";
        $tune->add(new AbcMidiLine($correctedLine));
        return true;
    }

    /**
     * @param mixed $line
     * @return bool
     */
    public function validate($line) {
        if (!preg_match('/^%%MIDI\s+(chordvol|bassvol)\s+(\d+)$/i', trim($line), $matches)) {
            return false;
        }

        $volume = (int)$matches[2];
        return $volume >= 0 && $volume <= 127;
    }
}
