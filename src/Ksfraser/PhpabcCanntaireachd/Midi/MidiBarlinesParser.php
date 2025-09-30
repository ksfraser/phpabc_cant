<?php
namespace Ksfraser\PhpabcCanntaireachd\Midi;

use Ksfraser\PhpabcCanntaireachd\AbcMidiLine;
use Ksfraser\PhpabcCanntaireachd\AbcLineParser;

/**
 * Parser for %%MIDI nobarlines and barlines directives
 */
class MidiBarlinesParser implements AbcLineParser
{
    /**
     * @param mixed $line
     * @return bool
     */
    public function canParse($line) {
        return preg_match('/^%%MIDI\s+(no)?barlines$/i', trim($line));
    }

    /**
     * @param mixed $line
     * @param mixed $tune
     * @return bool
     */
    public function parse($line, $tune) {
        if (!preg_match('/^%%MIDI\s+(no)?barlines$/i', trim($line), $matches)) {
            return false;
        }

        $modifier = isset($matches[1]) ? $matches[1] : ''; // 'no' or empty

        // Create corrected MIDI barlines line
        $correctedLine = "%%MIDI " . ($modifier ? $modifier : '') . "barlines";
        $tune->add(new AbcMidiLine($correctedLine));
        return true;
    }

    /**
     * @param mixed $line
     * @return bool
     */
    public function validate($line) {
        return preg_match('/^%%MIDI\s+(no)?barlines$/i', trim($line));
    }
}
