<?php
namespace Ksfraser\PhpabcCanntaireachd;

/**
 * Class MidiAccompanimentProgramParser
 *
 * Parses %%MIDI accompanimentprogram directives from ABC files.
 * Follows SRP by handling only MIDI accompanimentprogram directive parsing.
 *
 * @requirement FR10 (MIDI directive parsing)
 * @uml
 * @startuml
 * class MidiAccompanimentProgramParser {
 *   + parse(line: string): int|null
 * }
 * @enduml
 */
class MidiAccompanimentProgramParser {
    /**
     * Parses a %%MIDI accompanimentprogram directive line.
     *
     * @param string $line The ABC line to parse.
     * @return int|null The accompanimentprogram value (0-127) or null if not a accompanimentprogram directive.
     * @requirement FR10
     */
    public function parse(string $line): ?int {
        $trimmed = trim($line);
        if (!preg_match('/^%%MIDI\s+accompanimentprogram\s+(\d+)$/', $trimmed, $matches)) {
            return null;
        }
        $program = (int)$matches[1];
        return ($program >= 0 && $program <= 127) ? $program : null;
    }
}
