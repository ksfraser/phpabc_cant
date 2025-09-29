<?php
namespace Ksfraser\PhpabcCanntaireachd\Midi;

/**
 * Parser for %%MIDI beat directive (basic velocity control)
 */
class MidiBeatParser implements AbcLineParser
{
    public function canParse(string $line): bool {
        return preg_match('/^%%MIDI\s+beat\s+/i', trim($line));
    }

    public function parse(string $line, AbcTune $tune): bool {
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

    public function validate(string $line): bool {
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
