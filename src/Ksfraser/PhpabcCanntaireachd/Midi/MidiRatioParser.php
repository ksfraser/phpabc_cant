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
    /**
     * @param mixed $line
     * @return bool
     */
    public function canParse($line) {
        return preg_match('/^%%MIDI\s+ratio\s+/i', trim($line));
    }

    /**
     * @param mixed $line
     * @param mixed $tune
     * @return bool
     */
    public function parse($line, $tune) {
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

    /**
     * @param mixed $line
     * @return bool
     */
    public function validate($line) {
        if (!preg_match('/^%%MIDI\s+ratio\s+(\d+)\s+(\d+)$/i', trim($line), $matches)) {
            return false;
        }

        $numerator = (int)$matches[1];
        $denominator = (int)$matches[2];

        return $numerator >= 1 && $denominator >= 1;
    }
}
