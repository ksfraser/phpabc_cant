<?php
namespace Ksfraser\PhpabcCanntaireachd\Midi;

use Ksfraser\PhpabcCanntaireachd\AbcMidiLine;
use Ksfraser\PhpabcCanntaireachd\AbcLineParser;

/**
 * Parser for %%MIDI beat directive (basic velocity control)
 * Extends MidiDirectiveParser for common MIDI functionality.
 *
 * @requirement FR10 (MIDI directive parsing)
 */
class MidiBeatParser extends MidiDirectiveParser implements AbcLineParser
{
    /**
     * @param mixed $line
     * @return bool
     */
    public function canParse($line) {
        return $this->isMidiDirective($line) && $this->getDirectiveType($line) === 'beat';
    }

    /**
     * @param mixed $line
     * @param mixed $tune
     * @return bool
     */
    public function parse($line, $tune) {
        if (!preg_match('/^%%MIDI\s+beat\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)/i', trim($line), $matches)) {
            return false;
        }

        $strongVelocity = (int)$matches[1];
        $mediumVelocity = (int)$matches[2];
        $weakVelocity = (int)$matches[3];
        $beatDivision = (int)$matches[4];

        // Validate velocity ranges (0-127 for MIDI)
        if ($strongVelocity < 0 || $strongVelocity > 127 ||
            $mediumVelocity < 0 || $mediumVelocity > 127 ||
            $weakVelocity < 0 || $weakVelocity > 127) {
            $tune->add(new AbcMidiLine("%% Invalid MIDI beat velocities: must be 0-127"));
            return true;
        }

        // Validate beat division (typically 1-16)
        if ($beatDivision < 1 || $beatDivision > 16) {
            $tune->add(new AbcMidiLine("%% Invalid MIDI beat division: $beatDivision (must be 1-16)"));
            return true;
        }

        // Create corrected MIDI beat line
        $correctedLine = "%%MIDI beat $strongVelocity $mediumVelocity $weakVelocity $beatDivision";
        $tune->add(new AbcMidiLine($correctedLine));
        return true;
    }

    /**
     * @param mixed $line
     * @return bool
     */
    public function validate($line) {
        if (!preg_match('/^%%MIDI\s+beat\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)$/i', trim($line), $matches)) {
            return false;
        }

        $strongVelocity = (int)$matches[1];
        $mediumVelocity = (int)$matches[2];
        $weakVelocity = (int)$matches[3];
        $beatDivision = (int)$matches[4];

        return $strongVelocity >= 0 && $strongVelocity <= 127 &&
               $mediumVelocity >= 0 && $mediumVelocity <= 127 &&
               $weakVelocity >= 0 && $weakVelocity <= 127 &&
               $beatDivision >= 1 && $beatDivision <= 16;
    }
}
