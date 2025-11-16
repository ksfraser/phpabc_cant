<?php
namespace Ksfraser\PhpabcCanntaireachd;

/**
 * Pass to standardize ABC file formatting and spacing.
 * Ensures consistent indentation, spacing, and line formatting in output files.
 *
 * @requirement FR10 (formatting standardization)
 * @uml
 * @startuml
 * class AbcFormattingPass {
 *   + process(lines: array): array
 *   + standardizeLine(line: string): string
 *   + standardizeDirectiveSpacing(line: string): string
 *   + standardizeHeaderSpacing(line: string): string
 * }
 * @enduml
 */
class AbcFormattingPass {
    /**
     * Processes the array of ABC lines to standardize formatting.
     *
     * @param array $lines The input ABC lines.
     * @return array The processed lines with standardized formatting.
     */
    public function process(array $lines): array {
        $formatted = [];
        foreach ($lines as $line) {
            $formatted[] = $this->standardizeLine($line);
        }
        return ['lines' => $formatted];
    }

    /**
     * Standardizes a single line's formatting.
     *
     * @param string $line The line to standardize.
     * @return string The standardized line.
     */
    private function standardizeLine(string $line): string {
        $trimmed = trim($line);

        // Handle empty lines
        if ($trimmed === '') {
            return '';
        }

        // Standardize directive spacing (%%directive value) but not MIDI directives
        if (preg_match('/^%%(?!MIDI)/', $trimmed)) {
            return $this->standardizeDirectiveSpacing($trimmed);
        }

        // Standardize header field spacing (X: value)
        if (preg_match('/^[A-Z]:/', $trimmed)) {
            return $this->standardizeHeaderSpacing($trimmed);
        }

        // Standardize voice definitions (V:voice params)
        if (preg_match('/^V:/', $trimmed)) {
            return $this->standardizeVoiceSpacing($trimmed);
        }

        // Standardize lyrics lines (w: lyrics)
        if (preg_match('/^w:/', $trimmed)) {
            return $this->standardizeLyricsSpacing($trimmed);
        }

        // Return other lines as-is (music lines, comments, etc.)
        return $line;
    }

    /**
     * Standardizes formatting directive spacing.
     * Preserves %%directive format (no space after %%)
     *
     * @param string $line The directive line.
     * @return string The standardized directive line.
     */
    private function standardizeDirectiveSpacing(string $line): string {
        // Ensure no extra spaces after %% - directive name should follow immediately
        // Change %%  score to %%score, but preserve %%score as-is
        $line = preg_replace('/^%%+\s+/', '%%', $line);
        return $line;
    }

    /**
     * Standardizes header field spacing.
     * Converts "X:value" to "X: value" (space after colon)
     *
     * @param string $line The header line.
     * @return string The standardized header line.
     */
    private function standardizeHeaderSpacing(string $line): string {
        // Ensure space after colon in header fields
        $line = preg_replace('/^([A-Z]):([^ ])/', '$1: $2', $line);
        return $line;
    }

    /**
     * Standardizes voice definition spacing.
     *
     * @param string $line The voice line.
     * @return string The standardized voice line.
     */
    private function standardizeVoiceSpacing(string $line): string {
        // Ensure space after colon in voice definitions
        $line = preg_replace('/^V:([^ ])/', 'V: $1', $line);
        return $line;
    }

    /**
     * Standardizes lyrics line spacing.
     *
     * @param string $line The lyrics line.
     * @return string The standardized lyrics line.
     */
    private function standardizeLyricsSpacing(string $line): string {
        // Ensure space after colon in lyrics lines
        $line = preg_replace('/^w:([^ ])/', 'w: $1', $line);
        return $line;
    }
}