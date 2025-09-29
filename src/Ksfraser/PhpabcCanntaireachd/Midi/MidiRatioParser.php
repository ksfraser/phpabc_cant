<?php
namespace Ksfraser\PhpabcCanntaireachd\Midi;

use Ksfraser\PhpabcCanntaireachd\AbcLineParser;
use Ksfraser\PhpabcCanntaireachd\AbcTune;
use Ksfraser\PhpabcCanntaireachd\AbcMidiLine;

/**
 * Parser for %%MIDI ratio directive (broken rhythm ratio)
 */
class MidiRatioParser implements AbcLineParser
{
    public function canParse(string $line): bool {
        return preg_match('/^%%MIDI\s+ratio\s+/i', trim($line));
    }

    public function parse(string $line, AbcTune $tune): bool {
        if (!preg_match('/^%%MIDI\s+ratio\s+(\d+)\s+(\d+)/i', trim($line), $matches)) {
            return false;
        }

        $numerator = (int)$matches[1];
        $denominator = (int)$matches[2];

        // Validate ratio values (should be positive integers, typically small)
        if ($numerator < 1 || $denominator < 1) {
            $tune->add(new AbcMidiLine("%% Invalid MIDI ratio: $numerator/$denominator (must be positive integers)"));
            return true;
        }

        // Create corrected MIDI ratio line
        $correctedLine = "%%MIDI ratio $numerator $denominator";
        $tune->add(new AbcMidiLine($correctedLine));
        return true;
    }

    public function validate(string $line): bool {
        if (!preg_match('/^%%MIDI\s+ratio\s+(\d+)\s+(\d+)$/i', trim($line), $matches)) {
            return false;
        }

        $numerator = (int)$matches[1];
        $denominator = (int)$matches[2];

        return $numerator >= 1 && $denominator >= 1;
    }
}
