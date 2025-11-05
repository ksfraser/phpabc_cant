<?php
namespace Ksfraser\PhpabcCanntaireachd;

/**
 * Class MidiAccompanimentVolumeParser
 *
 * Parses %%MIDI accompanimentvolume directives from ABC files.
 * Follows SRP by handling only MIDI accompanimentvolume directive parsing.
 *
 * @requirement FR10 (MIDI directive parsing)
 * @uml
 * @startuml
 * class MidiAccompanimentVolumeParser {
 *   + parse(line: string): int|null
 * }
 * @enduml
 */
class MidiAccompanimentVolumeParser {
    /**
     * Parses a %%MIDI accompanimentvolume directive line.
     *
     * @param string $line The ABC line to parse.
     * @return int|null The accompanimentvolume value (0-127) or null if not a accompanimentvolume directive.
     * @requirement FR10
     */
    public function parse(string $line): ?int {
        $trimmed = trim($line);
        if (!preg_match('/^%%MIDI\s+accompanimentvolume\s+(\d+)$/', $trimmed, $matches)) {
            return null;
        }
        $volume = (int)$matches[1];
        return ($volume >= 0 && $volume <= 127) ? $volume : null;
    }
}
