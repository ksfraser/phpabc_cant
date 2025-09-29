<?php
namespace Ksfraser\PhpabcCanntaireachd\Midi;

/**
 * Parser for %%MIDI nobarlines and barlines directives
 */
class MidiBarlinesParser implements AbcLineParser
{
    public function canParse(string $line): bool {
        return preg_match('/^%%MIDI\s+(no)?barlines$/i', trim($line));
    }

    public function parse(string $line, AbcTune $tune): bool {
        if (!preg_match('/^%%MIDI\s+(no)?barlines$/i', trim($line), $matches)) {
            return false;
        }

        $modifier = isset($matches[1]) ? $matches[1] : ''; // 'no' or empty

        // Create corrected MIDI barlines line
        $correctedLine = "%%MIDI " . ($modifier ? $modifier : '') . "barlines";
        $tune->add(new AbcMidiLine($correctedLine));
        return true;
    }

    public function validate(string $line): bool {
        return preg_match('/^%%MIDI\s+(no)?barlines$/i', trim($line));
    }
}
