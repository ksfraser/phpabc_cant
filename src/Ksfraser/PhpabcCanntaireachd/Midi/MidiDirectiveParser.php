<?php
namespace Ksfraser\PhpabcCanntaireachd\Midi;

/**
 * Abstract base class for MIDI directive parsers.
 * Provides common functionality for parsing %%MIDI directives.
 *
 * @requirement FR10 (MIDI directive parsing)
 * @uml
 * @startuml
 * abstract class MidiDirectiveParser {
 *   + isMidiDirective(line: string): bool
 *   + getDirectiveType(line: string): string|null
 * }
 * @enduml
 */
abstract class MidiDirectiveParser {

    /**
     * Checks if a line is a MIDI directive.
     *
     * @param string $line The line to check.
     * @return bool True if it's a MIDI directive.
     */
    protected function isMidiDirective(string $line): bool {
        return preg_match('/^%%MIDI\s+/', trim($line)) === 1;
    }

    /**
     * Gets the directive type from a MIDI line.
     *
     * @param string $line The MIDI directive line.
     * @return string|null The directive type or null.
     */
    protected function getDirectiveType(string $line): ?string {
        if (preg_match('/^%%MIDI\s+(\w+)/', trim($line), $matches)) {
            return $matches[1];
        }
        return null;
    }
}