<?php
namespace Ksfraser\PhpabcCanntaireachd\Midi;

use Ksfraser\PhpabcCanntaireachd\AbcMidiLine;
use Ksfraser\PhpabcCanntaireachd\AbcLineParser;

/**
 * Parser for %%MIDI gchordon and gchordoff directives
 */
class MidiGchordControlParser implements AbcLineParser
{
    /**
     * @param mixed $line
     * @return bool
     */
    public function canParse($line) {
        return preg_match('/^%%MIDI\s+gchord(on|off)$/i', trim($line));
    }

    /**
     * @param mixed $line
     * @param mixed $tune
     * @return bool
     */
    public function parse($line, $tune) {
        if (!preg_match('/^%%MIDI\s+gchord(on|off)$/i', trim($line), $matches)) {
            return false;
        }

        $state = $matches[1]; // 'on' or 'off'

        // Create corrected MIDI gchord control line
        $correctedLine = "%%MIDI gchord$state";
        $tune->add(new AbcMidiLine($correctedLine));
        return true;
    }

    /**
     * @param mixed $line
     * @return bool
     */
    public function validate($line) {
        return preg_match('/^%%MIDI\s+gchord(on|off)$/i', trim($line));
    }
}
