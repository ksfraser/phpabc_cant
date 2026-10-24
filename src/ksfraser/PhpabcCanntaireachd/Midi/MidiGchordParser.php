<?php

declare(strict_types=1);

namespace ksfraser\PhpabcCanntaireachd\Midi;

use ksfraser\PhpabcCanntaireachd\AbcMidiLine;
use ksfraser\PhpabcCanntaireachd\AbcLineParser;

/**
 * Parser for %%MIDI gchord directives
 */
class MidiGchordParser implements AbcLineParser
{
    /**
     * @param mixed $line
     * @return bool
     */
    public function canParse($line) {
        return preg_match('/^%%MIDI\s+gchord(?:\s|$)/i', trim($line));
    }

    /**
     * @param mixed $line
     * @param mixed $tune
     * @return bool
     */
    public function parse($line, $tune) {
        if (!preg_match('/^%%MIDI\s+gchord(?:\s+(.+))?/i', trim($line), $matches)) {
            return false;
        }

        $params = isset($matches[1]) ? trim($matches[1]) : '';

        $correctedLine = "%%MIDI gchord";
        if (!empty($params)) {
            $correctedLine .= " $params";
        }

        $tune->add(new AbcMidiLine($correctedLine));
        return true;
    }

    /**
     * @param mixed $line
     * @return bool
     */
    public function validate($line) {
        // Basic validation - gchord directive exists
        return preg_match('/^%%MIDI\s+gchord/i', trim($line));
    }
}
