<?php
namespace Ksfraser\PhpabcCanntaireachd;

/**
 * Parser for %%MIDI gchordon and gchordoff directives
 */
class MidiGchordControlParser implements AbcLineParser
{
    public function canParse(string $line): bool {
        return preg_match('/^%%MIDI\s+gchord(on|off)$/i', trim($line));
    }

    public function parse(string $line, AbcTune $tune): bool {
        if (!preg_match('/^%%MIDI\s+gchord(on|off)$/i', trim($line), $matches)) {
            return false;
        }

        $state = $matches[1]; // 'on' or 'off'

        // Create corrected MIDI gchord control line
        $correctedLine = "%%MIDI gchord$state";
        $tune->add(new AbcMidiLine($correctedLine));
        return true;
    }

    public function validate(string $line): bool {
        return preg_match('/^%%MIDI\s+gchord(on|off)$/i', trim($line));
    }
}
