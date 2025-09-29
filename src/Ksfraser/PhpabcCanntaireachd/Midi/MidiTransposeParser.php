<?php
namespace Ksfraser\PhpabcCanntaireachd\Midi;

use Ksfraser\PhpabcCanntaireachd\AbcLineParser;
use Ksfraser\PhpabcCanntaireachd\AbcTune;
use Ksfraser\PhpabcCanntaireachd\AbcMidiLine;

/**
 * Parser for %%MIDI transpose and rtranspose directives
 */
class MidiTransposeParser implements AbcLineParser
{
    public function canParse(string $line): bool {
        return preg_match('/^%%MIDI\s+(?:r)?transpose\s+/i', trim($line));
    }

    public function parse(string $line, AbcTune $tune): bool {
        if (!preg_match('/^%%MIDI\s+(r?transpose)\s+(-?\d+)/i', trim($line), $matches)) {
            return false;
        }

        $type = $matches[1]; // 'transpose' or 'rtranspose'
        $semitones = (int)$matches[2];

        // Validate reasonable transposition range (-24 to +24 semitones = 2 octaves)
        if ($semitones < -24 || $semitones > 24) {
            // Invalid transposition - add as comment for user awareness
            $tune->add(new AbcMidiLine("%% Invalid MIDI $type: $semitones semitones (range: -24 to +24)"));
            return true;
        }

        // Create corrected MIDI transpose line
        $correctedLine = "%%MIDI $type $semitones";
        $tune->add(new AbcMidiLine($correctedLine));
        return true;
    }

    public function validate(string $line): bool {
        if (!preg_match('/^%%MIDI\s+(r?transpose)\s+(-?\d+)$/i', trim($line), $matches)) {
            return false;
        }

        $semitones = (int)$matches[2];
        return $semitones >= -24 && $semitones <= 24;
    }
}
