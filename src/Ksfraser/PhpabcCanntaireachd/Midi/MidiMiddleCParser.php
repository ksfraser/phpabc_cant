<?php
namespace Ksfraser\PhpabcCanntaireachd\Midi;

/**
 * Parser for %%MIDI c directive (middle C pitch setting)
 */
class MidiMiddleCParser implements AbcLineParser
{
    public function canParse(string $line): bool {
        return preg_match('/^%%MIDI\s+c\s+/i', trim($line));
    }

    public function parse(string $line, AbcTune $tune): bool {
        if (!preg_match('/^%%MIDI\s+c\s+(\d+)/i', trim($line), $matches)) {
            return false;
        }

        $pitch = (int)$matches[1];

        // Validate reasonable MIDI pitch range (typically 48-84 for middle C area)
        if ($pitch < 0 || $pitch > 127) {
            // Invalid pitch - add as comment for user awareness
            $tune->add(new AbcMidiLine("%% Invalid MIDI middle C pitch: $pitch (must be 0-127)"));
            return true;
        }

        // Create corrected MIDI c line
        $correctedLine = "%%MIDI c $pitch";
        $tune->add(new AbcMidiLine($correctedLine));
        return true;
    }

    public function validate(string $line): bool {
        if (!preg_match('/^%%MIDI\s+c\s+(\d+)$/i', trim($line), $matches)) {
            return false;
        }

        $pitch = (int)$matches[1];
        return $pitch >= 0 && $pitch <= 127;
    }
}
