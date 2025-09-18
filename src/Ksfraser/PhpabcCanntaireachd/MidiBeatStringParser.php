<?php
namespace Ksfraser\PhpabcCanntaireachd;

/**
 * Parser for %%MIDI beatstring directives
 */
class MidiBeatStringParser implements AbcLineParser
{
    public function canParse(string $line): bool {
        return preg_match('/^%%MIDI\s+beatstring(?:\s|$)/i', trim($line));
    }

    public function parse(string $line, AbcTune $tune): bool {
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

    public function validate(string $line): bool {
        // Basic validation - beatstring directive exists
        return preg_match('/^%%MIDI\s+beatstring/i', trim($line));
    }
}
