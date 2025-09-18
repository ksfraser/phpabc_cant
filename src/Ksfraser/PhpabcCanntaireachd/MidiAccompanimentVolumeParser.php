<?php
namespace Ksfraser\PhpabcCanntaireachd;

/**
 * Parser for %%MIDI chordvol and bassvol directives
 */
class MidiAccompanimentVolumeParser implements AbcLineParser
{
    public function canParse(string $line): bool {
        return preg_match('/^%%MIDI\s+(chordvol|bassvol)\s+/i', trim($line));
    }

    public function parse(string $line, AbcTune $tune): bool {
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

    public function validate(string $line): bool {
        if (!preg_match('/^%%MIDI\s+(chordvol|bassvol)\s+(\d+)$/i', trim($line), $matches)) {
            return false;
        }

        $volume = (int)$matches[2];
        return $volume >= 0 && $volume <= 127;
    }
}
