<?php
namespace Ksfraser\PhpabcCanntaireachd\Midi;

/**
 * Parser for %%MIDI gchord directives
 */
class MidiGchordParser implements AbcLineParser
{
    public function canParse(string $line): bool {
        return preg_match('/^%%MIDI\s+gchord(?:\s|$)/i', trim($line));
    }

    public function parse(string $line, AbcTune $tune): bool {
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

    public function validate(string $line): bool {
        // Basic validation - gchord directive exists
        return preg_match('/^%%MIDI\s+gchord/i', trim($line));
    }
}
