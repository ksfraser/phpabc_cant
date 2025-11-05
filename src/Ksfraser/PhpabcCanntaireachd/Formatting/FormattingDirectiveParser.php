<?php
namespace Ksfraser\PhpabcCanntaireachd\Formatting;

/**
 * Abstract base class for formatting directive parsers.
 * Provides common functionality for parsing formatting directives like %%landscape, %%pagewidth, etc.
 *
 * @requirement FR10 (formatting directive parsing)
 * @uml
 * @startuml
 * abstract class FormattingDirectiveParser {
 *   + isFormattingDirective(line: string): bool
 *   + getDirectiveType(line: string): string|null
 * }
 * @enduml
 */
abstract class FormattingDirectiveParser {
    /**
     * Checks if a line is a formatting directive.
     *
     * @param string $line The line to check.
     * @return bool True if it's a formatting directive.
     */
    protected function isFormattingDirective(string $line): bool {
        return preg_match('/^%%\s*\w+/', trim($line)) === 1 && !preg_match('/^%%MIDI\s+/', trim($line));
    }

    /**
     * Gets the directive type from a formatting line.
     *
     * @param string $line The formatting directive line.
     * @return string|null The directive type or null.
     */
    protected function getDirectiveType(string $line): ?string {
        if (preg_match('/^%%\s*(\w+)/', trim($line), $matches)) {
            return $matches[1];
        }
        return null;
    }
}