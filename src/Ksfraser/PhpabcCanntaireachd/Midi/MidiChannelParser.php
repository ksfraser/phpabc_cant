<?php
namespace Ksfraser\PhpabcCanntaireachd\Midi;

use Ksfraser\PhpabcCanntaireachd\AbcMidiLine;
use Ksfraser\PhpabcCanntaireachd\AbcLineParser;

/**
 * Parser for %%MIDI channel directives
 */
class MidiChannelParser implements AbcLineParser
{
    /**
     * @param mixed $line
     * @return bool
     */
    public function canParse($line) {
        return preg_match('/^%%MIDI\s+channel\s+/i', trim($line));
    }

    /**
     * @param mixed $line
     * @param mixed $tune
     * @return bool
     */
    public function parse($line, $tune) {
        if (!preg_match('/^%%MIDI\s+channel\s+(\d+)/i', trim($line), $matches)) {
            return false;
        }

        $channel = (int)$matches[1];

        // Validate channel range (1-16 for MIDI)
        if ($channel < 1 || $channel > 16) {
            // Invalid channel - add as comment for user awareness
            $tune->add(new AbcMidiLine("%% Invalid MIDI channel: $channel (must be 1-16)"));
            return true;
        }

        // Create corrected MIDI channel line
        $correctedLine = "%%MIDI channel $channel";
        $tune->add(new AbcMidiLine($correctedLine));
        return true;
    }

    /**
     * @param mixed $line
     * @return bool
     */
    public function validate($line) {
        if (!preg_match('/^%%MIDI\s+channel\s+(\d+)$/i', trim($line), $matches)) {
            return false;
        }

        $channel = (int)$matches[1];
        return $channel >= 1 && $channel <= 16;
    }
}
