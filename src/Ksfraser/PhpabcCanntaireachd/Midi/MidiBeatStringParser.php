<?php
namespace Ksfraser\PhpabcCanntaireachd\Midi;

use Ksfraser\PhpabcCanntaireachd\AbcMidiLine;
use Ksfraser\PhpabcCanntaireachd\AbcLineParser;

/**
 * Parser for %%MIDI beatstring directives
 */
class MidiBeatStringParser implements AbcLineParser
{
    /**
     * @param mixed $line
     * @return bool
     */
    public function canParse($line) {
        return preg_match('/^%%MIDI\s+beatstring(?:\s|$)/i', trim($line));
    }

    /**
     * @param mixed $line
     * @param mixed $tune
     * @return bool
     */
    public function parse($line, $tune) {
        if (!preg_match('/^%%MIDI\s+beatstring(?:\s+(.+))?/i', trim($line), $matches)) {
            return false;
        }

        $params = isset($matches[1]) ? trim($matches[1]) : '';

        $correctedLine = "%%MIDI beatstring";
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
        // Basic validation - beatstring directive exists
        return preg_match('/^%%MIDI\s+beatstring/i', trim($line));
    }
}
